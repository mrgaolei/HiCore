<?php
//得到一个model类


//function M($name='',$class='Model') {
function M($name='',$class='Model', $dbkey=null) { //2010-09-29 fantom 修改
    
    static $_model = array();
    $name = ucfirst($name);//2010-10-18 fantom 修改
    $classname=$name.$class;
     //if(!isset($_model[$classname])){ 
       // $_model[$classname]   = new $classname(Hi::getDb());
   // }
    if(!isset($_model[$classname]) || $dbkey){ //2010-09-29 fantom 修改
            $_model[$classname]   = new $classname(Hi::getDb($dbkey));
    }
        
    return $_model[$classname];
}

function V($rule, $args=array() ,$msg=null){

    $argstmp=$args;
    if(!is_array($args)){
        $argstmp=array($args);      
    }
    return new ValidationRule($rule, $argstmp, $msg);
}

// 返回一个Setting
function S($key, $appid = 0) {
	return M('Setting')->get($key, $appid);
}

/**
 * return a Service
 * @param string $clientname
 * @param string $class
 * @return BaseService
 */
function SVC($classname) {
    if (!Hi::ini('SVC_ENABLE')) return false;
    if (Hi::ini('SVC_REMOTE')) {
	    $client = new BaseClient($classname, Hi::ini("SVC_APPID"), Hi::ini("SVC_APPKEY"), Hi::ini("SVC_URL"));
	    return $client;
    } else {
    	$route = explode('.', $classname);
        
	    $servicepath = $interfacepath = "";
        $servicename = $interfacename = "";
        for ($i = 0; $i < count($route); $i++) {
            $servicepath .= '/';
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
        define('SVC_ROOT', Hi::ini("SVC_URL"));
        
        $interfacepath = Hi::ini("SVC_URL") . '/service'. $interfacepath;
        $servicepath = Hi::ini("SVC_URL") . '/service' . $servicepath;
        
        if (!file_exists($interfacepath)) {
        	throw new Exception("Service $interfacename interface not found.", 4104);
        }
        
        require_once $interfacepath;
        require_once $servicepath;
        $service = new $servicename(Hi::ini("SVC_APPID"), Hi::ini("SVC_APPKEY"));
        return $service;
    }
}

function Seq($seqName, $tablepre = "seq_") {
	return false;
    $db = new Hidb('localhost', 'phpapp', '123456', 'app_tickets', 'utf8', 0, false, true, false);
    $db->query("REPLACE INTO `{$tablepre}{$seqName}` (stub) VALUES ('a')", '', true);
    if ($db->errno() == 1146) {
	$db->query("CREATE TABLE `{$tablepre}{$seqName}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stub` char(1) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `stub` (`stub`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;", '', true);
	$db->query("REPLACE INTO `{$tablepre}{$seqName}` (stub) VALUES ('a')", '', true);
    }
    return $db->insert_id();
}



