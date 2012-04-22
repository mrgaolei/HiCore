<?php

!defined('HICORE_PATH') && exit('Access Denied');

define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

class HiConsoleApp {

    var $get = array();
    var $post = array();
    protected $_app_config; // 系统配置文件

    function __construct($app_config) {
        $this->_app_config = $app_config;
        $this->_initConfig();
        Hi::replaceIni('app_config', $app_config);
        // 设置默认的时区
        date_default_timezone_set(Hi::ini('default_timezone') ? Hi::ini('default_timezone') : "Asia/Shanghai");

	if ($this->_app_config['CONTROLLER_ROOT']){
       		$this->controller_root=$this->_app_config['CONTROLLER_ROOT'];
       }else{
       		$this->controller_root=$this->approot.'/controller';
       }
       if ($this->_app_config['MODEL_ROOT']){
       		$this->model_root=$this->_app_config['MODEL_ROOT'];
       }else{
       		$this->model_root=$this->approot.'/model';
       }

        define('DB_TABLEPRE', Hi::ini('db_tablepre'));

        // 打开 session
        if (Hi::ini('runtime_session_start')) {
            session_start();
        }
	if (Hi::ini('SVC_ENABLE')) {
	    require_once HICORE_PATH . '/hicore/BaseService.php';
	    require_once HICORE_PATH . '/hicore/BaseDAO.php';
	    require_once HICORE_PATH . '/hilib/AppService.php';
	    require_once HICORE_PATH . '/hilib/BaseClient.php';
	}

        set_magic_quotes_runtime(0);

        $this->post = string::haddslashes($_POST);

        $this->get = string::haddslashes($_GET);

	if (!Hi::ini('db_disable')) {
	    $this->init_defaultdb(); //初始化默认的数据库配置
	}
    }

    function init_defaultdb() {
        $db = new Hidb(
                        Hi::ini('db_host'),
                        Hi::ini('db_user'),
                        Hi::ini('db_password'),
                        Hi::ini('db_name'),
                        Hi::ini('db_charset'),
                        Hi::ini('db_connect'),
                        Hi::ini('db_usetrans'),
                        Hi::ini('db_autocommit')
        );
        Hi::addDb($db, Hi::ini('db_name'), true);
    }

    /**
     * 初始化应用程序设置
     */
    protected function _initConfig() {
        #IFDEF DEBUG
        //HiLog::log('APP BEGIN', HiLog::DEBUG);
        #ENDIF
        $cache_id = $this->_app_config['APPID'] . '_app_config';
        // 载入配置文件
        if ($this->_app_config['CONFIG_CACHED']) {

            /**
             * 从缓存载入配置文件内容
             */
            // 构造缓存服务对象
            $backend = $this->_app_config['CONFIG_CACHE_BACKEND'];
            $settings = isset($this->_app_config['CONFIG_CACHE_SETTINGS'][$backend]) ? $this->_app_config['CONFIG_CACHE_SETTINGS'][$backend] : null;
            $cache = new $backend($settings);

            // 载入缓存内容

            $config = $cache->get($cache_id);

            if (!empty($config)) {
                Hi::replaceIni($config);
                return;
            }
        }

        // 没有使用缓存，或缓存数据失效
        $config = self::loadConfig($this->_app_config);
        if ($this->_app_config['CONFIG_CACHED']) {
            $cache->set($cache_id, $config);
        }

        Hi::replaceIni($config);
    }

    /**
     * 载入配置文件内容
     *
     * @param array $app_config
     *
     * @return array
     */
    static function loadConfig(array $app_config) {
        $config = array();
        $cfg = $app_config['CONFIG_DIR'];
        $run_mode = strtolower($app_config['RUN_MODE']);

        $defaultconfig = require(HICORE_PATH . '/config/default_config.php');

        $runappconfig = require($cfg . '/' . $run_mode . '_config.php');

        $config = array_merge($defaultconfig, $runappconfig);

        if ($app_config['APPSETTING_PROVIDER']) {
            $classname = $app_config['APPSETTING_PROVIDER'];
            $obj = new $classname();
            $appsetting = $obj->loadsetting();
            $config = array_merge($config, $appsetting);
        }

        return $config;
    }

    /*
     * 临时添加(命令行模式)
     * $className string 类名
     * $fuctionName string 方法名
     *fantom 2010-10-20
     */
    public function HiRun($className, $fuctionName) {
        $control = new $className();
        $status = $control->$fuctionName();
    }

    public function run($conroller, $action) {
	$a = $b = array();
	$control = new $conroller($a, $b);
	$status = $control->$action();
    }
}

