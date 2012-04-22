<?php
!defined('HICORE_PATH') && exit('Access Denied');
/**
 * 权限验证类
 * 使用方法：
 *
 * 1. 先实例化该类(类型，数据库实例)
 * $permission = new Permission(1, $this->db);
 *
 * 2. 按照用户或者用户组初始化（任选其一）
 * $permission->initbyuid($uid);
 * 或者
 * $permission->initbygroupid($groupid);
 *
 * 3. 进行功能校验（两种），返回布尔型
 * $permission->checkbyid($functionid);
 * 或者
 * $permission->checkbyentery($controller, $action);
 *
 * 4. 也可以返回所有可用功能清单，用于生成功能菜单(是否分层)
 * $list = $permission->getAvalible(true);
 *
 * @author mrgaolei
 */
class Permission {

	private $db;
	private $appid;
	private $inited = false;
	private $uid;
	private $groupid;
	private $_tn_user = 'user';
	private $_tn_function = 'menu';
	private $_tn_functionrel = 'functionrel';
	public $_tn_group = 'admingroup';

	/**
	 *
	 * @param int $functiontype 权限类型
	 */
	public function __construct($appid = null, Hidb $db = null) {
		if (is_null($db)) {
			global $db;
		}
		$this->db = $db;
		$this->appid = $appid;
	}

	public function initbyuid($uid) {
		if ($this->inited == true)
		return false;
		$this->uid = $uid;
		$sql = "SELECT u.groupid FROM `" . DB_TABLEPRE . "{$this->_tn_user}` u WHERE u.uid = '$uid'";
		$permission = $this->db->fetch_first($sql);
		$this->groupid = $permission['groupid'];
		$this->inited = true;
	}

	public function initbygroupid($groupid) {
		if ($this->inited == true)
		return false;
		$this->groupid = $groupid;
		$this->inited = true;
	}

	public function checkbyid(int $functionid) {
		$sql = "SELECT * FROM `" . DB_TABLEPRE . "{$this->_tn_functionrel}` WHERE groupid = '{$this->groupid}' AND functionid = '$functionid'";
		$permission = $this->db->fetch_first($sql);
		if ($permission)
		return true;
		else
		return false;
	}

	public function checkbyentery($controller, $action) {
		$sql = "SELECT * FROM `" . DB_TABLEPRE . "{$this->_tn_functionrel}` rel INNER JOIN `" . DB_TABLEPRE . "{$this->_tn_function}` fun ON rel.functionid = fun.functionid WHERE rel.groupid = '{$this->groupid}' AND fun.controller = '$controller' AND fun.action = '$action'";
		$permission = $this->db->fetch_first($sql);
		if ($permission)
		return true;
		else
		return false;
	}

	public function getAllMenu($fenceng = false) {
		$sql = "SELECT * FROM `".DB_TABLEPRE."{$this->_tn_function}`";
		$avalible = $this->db->fetch_by_sql($sql);
		if ($fenceng) {
			$avalible = self::fenCeng($avalible);
		}
		return $avalible;
	}

	public function getAvalible($fenceng = false) {
		//$sql = "SELECT fun.* FROM `" . DB_TABLEPRE . "{$this->_tn_functionrel}` rel INNER JOIN `" . DB_TABLEPRE . "{$this->_tn_function}` fun ON rel.functionid = fun.functionid WHERE rel.groupid = '{$this->groupid}' AND fun.isshowmenu = 1";
		$sql = "SELECT privilege FROM `".DB_TABLEPRE."{$this->_tn_group}` WHERE groupid = {$this->groupid}";
		$query = $this->db->query($sql);
		$privilege = $this->db->fetch_row($query);
		$privilege = $privilege[0];
		$privilege = explode(',', $privilege);
		$np = array();
		foreach ($privilege as $p) {
			$np[] = "'$p'";
		}
		$privilege = implode(',', $np);
		//$sql = "SELECT * FROM `".DB_TABLEPRE."{$this->_tn_function}` WHERE functionid IN ($privilege) AND appid = '$this->appid'";
		$sql = "SELECT * FROM `".DB_TABLEPRE."{$this->_tn_function}` WHERE functionkey IN ($privilege)";
		$avalible = $this->db->fetch_by_sql($sql);
		if(!empty($avalible)){
			$temps = array();
			foreach($avalible as $value){
				$sql = "SELECT * FROM `".DB_TABLEPRE."{$this->_tn_function}` WHERE menuid = $value[parentid]";
				$temp = $this->db->fetch_by_sql($sql);
				foreach($temp as $value){
					$temps[] = $value;
				}
			}

			$catelist = array();
			foreach($temps as $value){
				if($value['parentid'] != null){
					$sql = "SELECT * FROM `".DB_TABLEPRE."{$this->_tn_function}` WHERE menuid = $value[parentid]";
					$temp = $this->db->fetch_by_sql($sql);
					foreach($temp as $value){
						$catelist[] = $value;
					}
				}else{
					$catelist[] = $value;
				}
			}
		}
		
		$list = array_merge($temps,$catelist);
		$avalible = array_merge($list,$avalible);
		$lists = array();
		foreach($avalible as $value){
			$lists[$value['menuid']] = $value;
		}
		if ($fenceng) {
			$avalible = self::fenCeng($lists);
		}
		return $avalible;
	}


	private static function fenCeng($array, $parentid = null) {
		$temp = array();
		foreach ($array as $c) {
			if ($c['parentid'] == $parentid) {
				//$c['subcat'] = self::fenCeng($array, $c['functionid']);
				$c['subcat'] = self::fenCeng($array, $c['menuid']);
				$temp[] = $c;
			}
		}
		return $temp;
	}

}
