<?php
/**
 *    访问者基础类，集合了当前访问用户的操作
 *
 *
 *    @return    void
 */
class BaseVisitor
{
	var $has_login = false;
	var $info      = null;
	// var $privilege = null;
	var $_info_key = '';
	
	var $uid;
	var $memkey;
	
	# var $memcache;
	
	var $remember = true;
	var $callback_service = null;
	var $callback_method = null;
	
	function __construct() {
		$this->BaseVisitor();
	}
	
	function BaseVisitor() {
		/*
		$this->memcache = new Memcache();
		if (!@$this->memcache->connect(Hi::ini('AUTH_SERVER/host'), Hi::ini('AUTH_SERVER/port'))) {
			throw new Exception("Cann't connect to Auth-Memcache Server ".Hi::ini('AUTH_SERVER/host').":".Hi::ini('AUTH_SERVER/port').".", 4001);
		}
		*/
		$this->uid = $_COOKIE[Hi::ini('AUTH_SERVER/cookiename')];
		if ($this->uid) {
			$this->uid = util::decrypt(Hi::ini('AUTH_SERVER/authkey'), $this->uid);
			if (is_numeric($this->uid)) {
				$this->memkey = Hi::ini('AUTH_SERVER/cookiename')."_{$this->uid}";
				$userinfo = Hi::cache($this->memkey);
				# $userinfo = $this->memcache->get($this->memkey);
				if (!$userinfo && $this->callback_service && $this->callback_method) {
					$method = $this->callback_method;
					$userinfo = SVC($this->callback_service)->$method($this->uid);
				}
			}
		}
		if (!empty($userinfo)) {
			$this->info = $userinfo;
			$this->has_login = true;
		} else {
			$this->info = array(
				'user_id' => 0,
				'user_name' => '匿名用户',
			);
			$this->has_login = false;
		}
	}
	
	function initassign($user_info) {
		if ($this->remember) {
			$time = time() + Hi::ini('AUTH_SERVER/cookietimeout');
		} else {
			$time = 0;
		}
		if ($user_info['user_id']) {
			$uid = util::encrypt(Hi::ini('AUTH_SERVER/authkey'), $user_info['user_id']);
			setcookie(Hi::ini('AUTH_SERVER/cookiename'), $uid, $time, '/', Hi::ini('AUTH_SERVER/cookiedomain'));
			$this->memkey = Hi::ini('AUTH_SERVER/cookiename')."_{$user_info['user_id']}";
			Hi::writeCache($this->memkey, $user_info, array("life_time" => 0));
			# $this->memcache->set($this->memkey, $user_info);
		}
	}
	
	function modify($field, $value) {
		if ($field == 'user_id') return;
		if ($field == 'username') $field = 'user_name';
		if ($this->memkey) {
			$info = Hi::cache($this->memkey);
			# $info = $this->memcache->get($this->memkey);
			$info[$field] = $value;
			Hi::writeCache($this->memkey, $info, array("life_time" => 0));
			# $this->memcache->set($this->memkey, $info);
		}
	}

	/**
	 *    获取当前登录用户的详细信息
	 *
	 *    @author    Garbin
	 *    @return    array      用户的详细信息
	 */
	function get_detail() {
		/* 未登录，则无详细信息 */
		if (!$this->has_login) {
			return array();
		}

		/* 取出详细信息 */
		static $detail = null;

		if ($detail === null) {
			$detail = $this->_get_detail();
		}

		return $detail;
	}

	/**
	 *    获取用户详细信息
	 *
	 *    @author    Garbin
	 *    @return    array
	 */
	function _get_detail() {
		/*

		$model_member =& m('member');

		// 获取当前用户的详细信息，包括权限
		$member_info = $model_member->findAll(array(
		'conditions'    => "member.user_id = '{$this->info['user_id']}'",
		'join'          => 'has_store',                 //关联查找看看是否有店铺
		'fields'        => 'email, password, real_name, logins, ugrade, portrait, store_id, state, feed_config',
		'include'       => array(                       //找出所有该用户管理的店铺
		'manage_store'  =>  array(
		'fields'    =>  'user_priv.privs, store.store_name',
		),
		),
		));
		$detail = current($member_info);

		/// 如果拥有店铺，则默认管理的店铺为自己的店铺，否则需要用户自行指定
		if ($detail['store_id'] && $detail['state'] != STORE_APPLYING) // 排除申请中的店铺
		{
		$detail['manage_store'] = $detail['has_store'] = $detail['store_id'];
		}
		*/
		$detail=array();

		return $detail;
	}

	/**
	 *    获取当前用户的指定信息
	 *
	 *    @author    Garbin
	 *    @param     string $key  指定用户信息
	 *    @return    string  如果值是字符串的话
	 *               array   如果是数组的话
	 */
	function get($key = null)
	{
		$info = null;

		if (empty($key))
		{
			/* 未指定key，则返回当前用户的所有信息：基础信息＋详细信息 */
			$info = array_merge((array)$this->info, (array)$this->get_detail());
		}
		else
		{
			/* 指定了key，则返回指定的信息 */
			if (isset($this->info[$key]))
			{
				/* 优先查找基础数据 */
				$info = $this->info[$key];
			}
			else
			{
				/* 若基础数据中没有，则查询详细数据 */
				$detail = $this->get_detail();
				$info = isset($detail[$key]) ? $detail[$key] : null;
			}
		}

		return $info;
	}

	/**
	 *    登出
	 *
	 *    @author    Garbin
	 *    @return    void
	 */
	function logout() {
		if ($this->memkey) {
			Hi::cleanCache($this->memkey);
			# $this->memcache->delete($this->memkey);
		}
		setcookie(Hi::ini('AUTH_SERVER/cookiename'), null, 0, '/', Hi::ini('AUTH_SERVER/cookiedomain'));
	}

	/*


	function i_can($event, $privileges = array())
	{
	$fun_name = 'check_' . $event;

	return $this->$fun_name($privileges);
	}

	function check_do_action($privileges)
	{
	$mp = APP . '|' . ACT;

	if ($privileges == 'all')
	{
	///拥有所有权限
	return true;
	}
	else
	{
	// 查看当前操作是否在白名单中，如果在，则允许，否则不允许
	$privs = explode(',', $privileges);
	if (in_array(APP . '|all', $privs) || in_array($mp, $privs))
	{
	return true;
	}

	return false;
	}
	}
	*/

}
