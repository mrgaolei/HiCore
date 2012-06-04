<?php

!defined('HICORE_PATH') && exit('Access Denied');

define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

class HiApp {

    var $get = array();
    var $post = array();
    var $querystring;
    private $control;
    var $controller;
    var $action;
    private $approot;
    //private $urlmode;
    
    private $controller_root;
    private $model_root;
    private $view_root;
    private $obj_root;
          
    protected $_app_config; // ϵͳ�����ļ�

    function  __construct($app_config) {
    	
       $this->_app_config = $app_config;
        $this->_initConfig();
        Hi::replaceIni('app_config', $app_config);

         // ����Ĭ�ϵ�ʱ��
        
        date_default_timezone_set(Hi::ini('default_timezone')?Hi::ini('default_timezone'):"Asia/Shanghai");
       
       
       $this->approot = $this->_app_config['APP_ROOT'];
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
      if ($this->_app_config['VIEW_ROOT']){
       		$this->view_root=$this->_app_config['VIEW_ROOT'];
       }elseif (Hi::ini('tpldir_path') != ''){
       		$this->view_root=Hi::ini('tpldir_path');
       }else{
           $this->view_root=$this->approot.'/view';
       }
       if ($this->_app_config['OBJ_ROOT']){
       		$this->obj_root=$this->_app_config['OBJ_ROOT'];
       }elseif(Hi::ini('objdir_path') != ''){
            $this->obj_root=Hi::ini('objdir_path');
       }else{
       		$this->obj_root=$this->approot.'/data/view';
       }
       
        
       
        
       	define('DB_TABLEPRE',Hi::ini('db_tablepre'));  

    	// �� session
        if (Hi::ini('runtime_session_start'))
        {
            session_start();
        }        
        
	if (Hi::ini('SVC_ENABLE')) {
	    require_once HICORE_PATH . '/hicore/BaseService.php';
	    require_once HICORE_PATH . '/hicore/BaseDAO.php';
	    require_once HICORE_PATH . '/hilib/AppService.php';
	    require_once HICORE_PATH . '/hilib/BaseClient.php';
	}

        // ����������·��
        Hi::importcontroller($this->controller_root);
        Hi::importmodel($this->model_root);
        //Hi::import($this->approot);        
        
        $this->init_request();
	if (!Hi::ini('db_disable')) {
	    $this-> init_defaultdb(); //��ʼ��Ĭ�ϵ���ݿ�����
	}
		
    }

    function init_defaultdb() {
    	if (!Hi::ini('db_dsn')) {
    		$charset = Hi::ini('db_charset');
    		$charset == 'utf-8' && $charset = 'utf8';
    		$dsn = "mysql:host=".Hi::ini('db_host').";dbname=".Hi::ini('db_name').";charset=".$charset;
    	} else {
    		$dsn = Hi::ini('db_dsn');
    	}
    	
    	$db = new HiPDO($dsn, Hi::ini('db_user'), Hi::ini('db_password'), null, Hi::ini('db_usetrans'),
        	Hi::ini('db_autocommit'));
    	/*
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
        	*/
        Hi::addDb($db,$dsn,true);
        
    }
    
    
  	/**
     * ��ʼ��Ӧ�ó�������
     */
    protected function _initConfig()
    {
        #IFDEF DEBUG
       	//HiLog::log('APP BEGIN', HiLog::DEBUG);
       
        #ENDIF
        $cache_id = $this->_app_config['APPID'] . '_app_config';
        // ���������ļ�
        if ($this->_app_config['CONFIG_CACHED'])
        {
           
            /**
             * �ӻ������������ļ�����
             */

            // ���컺��������
            $backend = $this->_app_config['CONFIG_CACHE_BACKEND'];
            $settings = isset($this->_app_config['CONFIG_CACHE_SETTINGS'][$backend]) ? $this->_app_config['CONFIG_CACHE_SETTINGS'][$backend] : null;
            $cache = new $backend($settings);

            // ���뻺������
            
            $config = $cache->get($cache_id);

            if (!empty($config))
            {
                Hi::replaceIni($config);
                return;
            }
        }

        // û��ʹ�û��棬�򻺴����ʧЧ
        $config = self::loadConfig($this->_app_config);
        if ($this->_app_config['CONFIG_CACHED'])
        {
            $cache->set($cache_id, $config);
        }

        Hi::replaceIni($config);
    }
    
    
 /**
     * ���������ļ�����
     *
     * @param array $app_config
     *
     * @return array
     */
    static function loadConfig(array $app_config)
    {
	$config=array();
        $cfg = $app_config['CONFIG_DIR'];
        $run_mode = strtolower($app_config['RUN_MODE']); 

        $defaultconfig = require(HICORE_PATH . '/config/default_config.php');
        
       	$runappconfig = require($cfg .'/'.$run_mode.'_config.php');

        $config = array_merge($defaultconfig, $runappconfig);

        if($app_config["APPSETTING_PROVIDER"]){
            $classname=$app_config["APPSETTING_PROVIDER"];
            $obj=new $classname();
            $appsetting=$obj->loadsetting();
            $config = array_merge($config, $appsetting);
           
        }
        
        return $config;
    }
    

    function init_request() {
    	
    	//set_magic_quotes_runtime(0);
    	
       // global $encoding;
        $querystring=$_SERVER['QUERY_STRING'];
        $pos = strpos($querystring , '.');
        if($pos!==false) {
            $querystring=substr($querystring,0,$pos);
        }
        if (preg_match("/^\/index\.php/i", $_SERVER['REQUEST_URI'])) {
        	Hi::changeIni('url_mode', 2);
        }
        if (Hi::ini('url_mode') == 1) {
            $querystrings = explode('/' , $querystring);
            foreach ($querystrings as $qs) {
                if ($qs != '') $this->get[] = $qs;
            }
            unset ($querystrings, $qs);
            if(empty($this->get[0])) {
                $this->get[0]='index';
            }
            if(empty($this->get[1])) {
                $this->get[1]='default';
            }
            if(count($this->get)<2) {
                exit(' Access Denied !');
            }
            $this->get=string::haddslashes($this->get,1);
            $this->control = $this->get[0];
            $this->controller=ucwords($this->get[0]).'Controller';
            $this->action=$this->get[1];
            $gets = array();
            for ($i = 2; $i < count($this->get); $i = $i + 2) {
                $gets[$this->get[$i]] = $this->get[$i+1];
            }
            $this->get = $gets;
        } elseif (Hi::ini('url_mode') == 2) {
            if ($_GET) {
                foreach ($_GET as $k => $v) {
                    $this->get[$k] = $v;
                }
            }
            if ($this->get['c']) {
                $this->control = $this->get['c'];
                $this->controller = ucwords($this->get['c']).'Controller';
            } elseif ($this->get['controller']) {
            	$this->control = $this->get['controller'];
                $this->controller = ucwords($this->get['controller']).'Controller';
            } else {
                $this->control = 'index';
                $this->controller = 'IndexController';
            }
            if ($this->get['a']) {
                $this->action = $this->get['a'];
            } elseif ($this->get['action']) {
            	$this->action = $this->get['action'];
            } else {
                $this->action = 'default';
            }
        } elseif (Hi::ini('url_mode') == 3) {
        	$querystring = $_SERVER['QUERY_STRING'];
        	$querystrings = explode('/', $querystring);
        	$pars = array();
        	foreach ($querystrings as $k => $v) {
        		if (trim($v)) {
        			$pars[] = $v;
        		}
        	}
			$querystrings = $pars;
        	switch (count($querystrings)) {
        		case 0:
        			# ��ȫĬ�ϵ�c��a
        			$this->control = 'index';
        			$this->controller = 'IndexController';
        			$this->action = 'default';
        			break;
        		case 1:
        			$this->get = self::urlmode3params($querystrings[0]);
        			if (is_array($this->get)) {
        				$this->control = 'index';
        				$this->controller = 'IndexController';
        				$this->action = 'default';
        			} else {
        				$this->control = strtolower($querystrings[0]);
        				$this->controller = ucfirst($querystrings[0]).'Controller';
        				$this->action = 'default';
        				$this->get = array();
        			}
        			break;
        		case 2:
        			$this->control = strtolower($querystrings[0]);
        			$this->controller = ucfirst($querystrings[0]).'Controller';
        			$this->get = self::urlmode3params($querystrings[1]);
        			if (is_array($this->get)) {
        				$this->action = 'default';
        			} else {
        				$this->action = $querystrings[1];
        				$this->get = array();
        			}
        			break;
        		case 3:
        			# 0��c��1��a��2������
        			$this->control = strtolower($querystrings[0]);
        			$this->controller = ucfirst($querystrings[0]).'Controller';
        			$this->action = strtolower($querystrings[1]);
        			$this->get = self::urlmode3params($querystrings[2]);
        			break;
        		default:
        			break;
        	}
        } else {
        	die ('URL_MODE does not supported!');
        }
        $this->post=string::haddslashes($_POST);
        unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);
        //$encoding = CMS_CHARSET;
        
        unset($_GET,$_POST,$gets);
    }
/*
    function load_control() {
    	
    	$controlfile=$this->controller_root.'/'.$this->controller.'.php';
    
        if(false===@include($controlfile)) {
            $this->notfound('controller "'.$this->controller.'"  not found!');
        }
    }
*/
    function run() {
    	
    	
        
        $control = new $this->controller($this->get,$this->post,$this->model_root,$this->view_root,$this->obj_root);
        
        $control->str_controller = strtolower(substr($this->controller, 0, strlen($this->controller) - 10));
        $control->str_action = $this->action;
        

        if ($this->querystring) {
            $control->hsetcookie('querystring',$this->querystring, 3600);
        }

        if ($control->user['uid'] == 0	&& $control->setting['close_website'] === '1'	&& strpos('dologin,dologout,docheckusername,docheckcode,docode',$method) === false
        ) {
            @header('Content-type: text/html; charset='.Hi::ini('response_charset'));
            exit($control->setting['close_website_reason']);
        }

        $method = 'do'.$this->action;
        if(method_exists($control, $method)) {
            $regular=$this->control.'/'.$this->action;
            $isadmin= ('admin'==substr($this->controller,0,5));
            
            if($control->checkable($this->control, $this->action)) {
                $control->$method();
            }else {
                $control->message('Controller: '.$this->controller.', Action: '.$this->action.'.<br />'.$regular.' ���ʱ��ܾ�','', $isadmin);
            }
        }else {
            $this->notfound('method "'.$method.'" not found!');
        }
        
        Hi::closeSharddb_mlink();//�ر����е���ݿ�����
    }

    function notfound($error) {
    
        @header('HTTP/1.0 404 Not Found');
        exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p> $error </p></body></html>");
    }
    
    public static function urlmode3params($parmstr) {
    	if (!preg_match("/\.html$/i", $parmstr)) return false;
    	$parmstr = preg_replace("/\.html$/i", "", $parmstr);
    	$parmstrs = explode('-', $parmstr);
    	$gets = array();
    	for ($i = 0; $i < count($parmstrs); $i = $i + 2) {
    		if (!$parmstrs[$i]) continue;
    		$gets[$parmstrs[$i]] = str_replace('~', '-', $parmstrs[$i + 1]);
    	}
    	if (count($gets)) {
    		return $gets;
    	} else {
    		return false;
    	}
    }

}

