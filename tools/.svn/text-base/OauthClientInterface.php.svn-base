<?php
interface OauthClientInterface {

	function __construct($akey, $skey, $access_token = null, $token_secret = null);

	/**
	 * 获取登录页地址
	 * @param string $callbackurl 回调页Url
	 * @return 登录页地址
	 */
	function getLoginUrl($callbackurl);

	/**
	 * 获取登录用户相关信息
	 * @param array $request 回调返回的内容，一般为$_REQUEST
	 * @return array(
	 * 		'oauth_token' => [], 
	 * 		'oauth_token_secret' => [], 
	 * 		'user_id' => [], 
	 * 		'name' => [], 
	 * )
	 */
	function getUserInfo($request = null);

	/**
	 * 发微博
	 * @param $content 微博内容
	 * @param $img 图片
	 * @param $ip IP
	 * @param $jing 位置经度
	 * @param $wei 位置纬度
	 */
	function add_t($content, $img = null, $ip = null, $jing = null, $wei = null);

}