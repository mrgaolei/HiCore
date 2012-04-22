<?php

/**
 * Description of AppService
 *
 * @author mrgaolei
 */
class AppService {

    private $appid;
    private $appkey;
    private $url;

    public function __construct($appid, $appkey, $url) {
	$this->appid = $appid;
	$this->appkey = $appkey;
	$this->url = $url;
    }

    public function call($class, $method, $param = array()) {
	$url = "&route={$class}:{$method}";
	$str = $this->formpost($param, $url);
	$result = unserialize($str);
	if ($result === false) {
	    return array('errorid' => 1, 'errormessage' => $str);
	} else {
	    return $result;
	}
    }

    private function formpost($data, $url) {
    	$data['appid'] = $this->appid;
    	//$data['version'] = $this->version;
    	$alldata = "";
    	foreach ($data as $k => $v) {
    		$alldata .= $v;
    	}
    	$data['key'] = md5($alldata . $this->appkey);
        $ch = curl_init($this->url . $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    public function getAppid() {
	return $this->appid;
    }

}