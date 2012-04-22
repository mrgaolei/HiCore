<?php
require_once 'OauthClientInterface.php';

class QQClient implements OauthClientInterface {
	
	private $appid;
	private $key;
	private $access_token;
	private $openid;
	
	function __construct($akey, $skey, $access_token = null, $token_secret = null) {
		$this->appid = $akey;
		$this->key = $skey;
		$this->access_token = $access_token;
		$this->openid = $token_secret;
	}
	
	function getLoginUrl($callbackurl) {
		$_SESSION['state'] = md5(uniqid(rand(), TRUE));
		$data = array(
			'response_type'=>'code', 'client_id'=>$this->appid, 'redirect_uri'=>urlencode($callbackurl), 'state'=>$_SESSION['state'], 
			'scope'=>'get_user_info,add_t,add_pic_t', 
		);
		return 'https://graph.qq.com/oauth2.0/authorize?'.$this->mkHttpStr($data);
	}
	
	function getUserInfo($request = null) {
		if (!$request) $request = $_REQUEST;
		if ($request['state'] != $_SESSION['state']) return 0;
		$data = array(
			'grant_type'=>'authorization_code', 'client_id'=>$this->appid, 'client_secret'=>$this->key, 
			'code'=>$request['code'], 'state'=>$_SESSION['state'], 'redirect_uri'=>urlencode($_SESSION['callbackurl']), 
		);
		$r = $this->get('https://graph.qq.com/oauth2.0/token', $data);
		$r = $this->qstr2arr($r);
		$this->access_token = $r['access_token'];
		$r = $this->get('https://graph.qq.com/oauth2.0/me?access_token='.$this->access_token);
		$r = trim($r, "callback();\r\n ");
		$r = json_decode($r, true);
		$this->openid = $r['openid'];
		$r = $this->get('https://graph.qq.com/user/get_user_info', $this->mergeParam(array('format'=>'json')));
		$r = json_decode($r, true);
		return array(
			'oauth_token'=>$this->access_token, 'oauth_token_secret'=>$this->openid, 
			'user_id'=>$this->openid, 'name'=>$r['nickname'], 
		);
	}
	
	function add_t($content, $img = null, $ip = null, $jing = null, $wei = null) {
		$url = 'https://graph.qq.com/t/add_t';
		$data = array(
			'format' => 'json',
			'content' => $content,
			'clientip' => $ip,
			'jing' => $jing,
			'wei' => $wei,
		);
		if ($img) {
			$file = Hi::ini('cache_path').'/mybaby_qq_img_'.time().mt_rand(100, 999).trim(strrchr($img, '/'), '/ ');
			file_put_contents($file, file_get_contents($img));
			$data['pic'] = '@'.$file;
			$url = 'https://graph.qq.com/t/add_pic_t';
		}
		$data = $this->mergeParam($data);
		$ret = $this->post($url, $data);
		return $ret;
	}
	
	private function mergeParam($data) {
		$param = array(
			'access_token' => $this->access_token,
			'oauth_consumer_key' => $this->appid,
			'openid' => $this->openid,
		);
		if (is_array($data)) $param = array_merge($data, $param);
		return $param;
	}
	
	private function mkHttpStr($data) {
		if (is_array($data)) {
			$dstr = '';
			foreach ($data as $k => $v) {
				$dstr .= "&$k=$v";
			}
			$data = trim($dstr, '& ');
		}
		return $data;
	}
	
	private function qstr2arr($s) {
		$r = array();
		$s = trim($s, '?&= ');
		$s = explode('&', $s);
		foreach ($s as $v) {
			$v = explode('=', $v);
			$r[$v[0]] = $v[1];
		}
		return $r;
	}
	
	private function get($url, $data = null) {
		if ($data) {
			$data = $this->mkHttpStr($data);
			$url = trim($url, '?& ');
			if (strpos($url, '?')) $url .= '&'.$data;
			else $url .= '?'.$data;
		}
		if (ini_get('allow_url_fopen') == '1') return file_get_contents($url);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $url);
		$ret =  curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
	
	private function post($url, $data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_URL, $url);
		$ret = curl_exec($ch);
		curl_close($ch);
		if ($data['pic']) {
			$data['pic'] = trim($data['pic'], '@ ');
			if (file_exists($data['pic'])) unlink($data['pic']);
		}
		return $ret;
	}
	
}