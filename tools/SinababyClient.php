<?php
require_once 'OauthClientInterface.php';
require_once 'saet2.ex.class.php';

class SinababyClient implements OauthClientInterface {
	
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
		/*$o = new WeiboOauth($this->appid, $this->key);
		$keys = $o->getRequestToken();
		$_SESSION['BIND_KEYS']['sina'] = $keys;
		return $o->getAuthorizeURL($keys['oauth_token'], false, $callbackurl);*/
	}
	
	function getUserInfo($request = null) {
		/*if (!$request) $request = $_REQUEST;
		$o = new WeiboOauth($this->appid, $this->key, $_SESSION['BIND_KEYS']['sina']['oauth_token'], $_SESSION['BIND_KEYS']['sina']['oauth_token_secret']);
		$last_key = $o->getAccessToken($request['oauth_verifier']);
		$c = new WeiboClient($this->appid, $this->key, $last_key['oauth_token'], $last_key['oauth_token_secret']);
		$me = $c->verify_credentials();
		$last_key['name'] = $me['name'];
		return $last_key;*/
	}
	
	function getSignedRequest($param){
		$o = new SaeTOAuth($this->appid, $this->key);
		return $o->parseSignedRequest($param);
	}
	
	function getaouth($access_token,$uid){
		$c = new SaeTClient($this->appid, $this->key,$access_token ,'');
		$showuser = $c->show_user($uid);
		$c->oauth->userinfo = $showuser;
		return $c->oauth;
	}
	
	
	function add_t($content, $img = null, $ip = null, $jing = null, $wei = null) {
		$c = new SaeTClient($this->appid, $this->key, $this->access_token, $this->openid);
		$content = urlencode($content);
		if ($img) return $c->upload($content, $img, $wei, $jing);
		else return $c->update($content, null, $wei, $jing);
	}

}