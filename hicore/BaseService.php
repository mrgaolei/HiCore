<?php

!defined('HICORE_PATH') && exit('Access Denied');

/**
 * Description of BaseService
 *
 * @author mrgaolei
 */
class BaseService {

    protected $appid;
    private $appkey;
    protected $db = array();

    public function  __construct($appid, $appkey) {
	$this->appid = $appid;
	$this->appkey = $appkey;
	$this->initialize();
    }

    public function initialize(){}

    public function getService($svcname) {
	$route = explode('.', $svcname);
	$servicepath = $interfacepath = "";
	$servicename = $interfacename = "";
	for ($i = 0; $i < count($route); $i ++) {
	    $servicepath .=  '/';
	    $interfacepath .= '/';
	    if ($i == count($route) - 1) {
		$interfacename = ucfirst("{$route[$i]}Service");
		$interfacepath .= ucfirst("{$interfacename}.php");
		$servicename = ucfirst("{$route[$i]}ServiceImpl");
		$servicepath .= "impl/".ucfirst($servicename).".php";
	    } else {
		$servicepath .= $route[$i];
		$interfacepath .= $route[$i];
	    }
	}
	require_once SVC_ROOT . '/service' . $interfacepath;
	require_once SVC_ROOT . '/service' . $servicepath;
	$service = new $servicename($this->appid, $this->appkey);
	return $service;
    }

    public function getDb($poolname = 'default') {
	if ($this->db[$poolname]) {
	    return $this->db[$poolname];
	} else {
	    $xml = SVC_ROOT . "/dbpool/$poolname.xml";
	    $xml = simplexml_load_file($xml);
	    $db = new Hidb($xml->server, $xml->username, $xml->password, $xml->dbname, $xml->charset,0,true,false,true);
	    $this->db[$poolname] = $db;
	    return $db;
	}
    }

    public function getDAO($name, $dbpoolname = 'default') {
	$name = explode('.', $name);
	$path = "";
	for ($i = 0; $i < count($name); $i ++) {
	    if ($i == count($name) - 1) {
		$classname = ucfirst($name[$i]) . 'DAO';
		$path .= $classname;
	    } else {
		$path .= strtolower($name[$i]) . '/';
	    }
	}
	require_once SVC_ROOT . "/dao/$path.php";
	$object = new $classname($this->getDb($dbpoolname));
	return $object;
    }

    public function getShardDAO($name) {
		$name = explode('.', $name);
		$path = "";
		for ($i = 0; $i < count($name); $i ++) {
		    if ($i == count($name) - 1) {
			$classname = ucfirst($name[$i]) . 'DAO';
			$path .= $classname;
		    } else {
			$path .= strtolower($name[$i]) . '/';
		    }
		}
		require_once SVC_ROOT . "/dao/$path.php";
		$object = new $classname();
		if (Hi::ini("DBNODE_PATH")) {
			$object->setDbnodePath(Hi::ini("DBNODE_PATH"));
		}
		return $object;
    }

    public function error($errorid, $errormessage = "") {
	return array(
	    'errorid' => $errorid,
	    'errormessage' => $errormessage,
	);
    }

    public function returnQueryBuilder(QueryBuilder $qb, $data) {
	return array(
	    'QueryBuilder' => $qb,
	    'data' => $data,
	);
    }

    protected function getHash($uid) {
	$md5 = md5($uid);
	$hash = substr($md5, 0, 1);
	$hash .= substr($md5, strlen($md5) - 1);
	return $hash;
    }
}
