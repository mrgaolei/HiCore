<?php

/**
 * Description of BaseClient
 *
 * @author mrgaolei
 */
class BaseClient {
    private $class;
    private $appService;

    public function __construct($class, $appid, $appkey, $url) {
	$this->appService = new AppService($appid, $appkey, $url);
        $this->class = $class;
    }

    public function  __call($name, $arguments) {
	$param = array();
	$i = 0;
	foreach($arguments as $a) {
	    $sa = serialize($a);
	    $param[md5($sa.$i)] = $sa;
	    $i ++;
	}
	return $this->appService->call($this->class, $name, $param);
    }
}