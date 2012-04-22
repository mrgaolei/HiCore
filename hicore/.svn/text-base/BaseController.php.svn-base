<?php

!defined('HICORE_PATH') && exit('Access Denied');

class BaseController {

    var $ip;
    var $time;
    var $db;
    /**
     *
     * @var HiTemplate
     */
    protected $view;
    var $cache;
    var $forward;
    var $user = array();
    //var $setting = array();
    // var $advertisement = array();
    // var $channel = array();
    // var $style = array();
    // var $plugin = array();
    var $get = array();
    var $post = array();
    protected $http_domain;
    protected $http_prefix;
    protected $model_root;
    protected $view_root;
    protected $obj_root;
    var $visitor;
    public $str_controller;
    public $str_action;
    protected $_validations = array();
    protected $_jsvalidations = array();
    
    # 通过controller定义head
    protected $head_title;
    protected $head_javascript = array();
    protected $head_css = array();
    

    /*     * * 数据的验证结果 *
     *   * @var boolean
     */
    protected $_is_valid = true;
    /**
     * 验证失败的信息
     *
     * @var string
     */
    protected $_failedhtml = array();


    function __construct(& $get, & $post, $model_root=NULL, $view_root=NULL, $obj_root=NULL) {
        $this->BaseController( $get,  $post, $model_root, $view_root, $obj_root);
        $this->head_title = Hi::ini('SITE_NAME');
        if (!$this->head_title) $this->head_title = Hi::ini('page_name');
        $this->setHeadTitle();
        $this->initialize();
    }
    
    /**
     * 设置网页标题栏
     * 需要模版有<title>$page_title</title>
     * @param string $title
     * @param boolean $append 是追加到后面还是放在前面
     * @param string $split 分割字符串
     * @param boolean $rewrite 是否完全覆盖配置文件
     */
    public function setHeadTitle($title = null, $append = false, $split = '&nbsp;', $rewrite = false) {
    	if (!is_null($title)) {
	    	if ($rewrite) {
	    		$this->head_title = $title;
	    	} else {
	    		if ($append) {
	    			$title = $split . $title;
	    			$this->head_title .= $title;
	    		} else {
	    			$this->head_title = $title . $split . $this->head_title;
	    		}
	    	}
    	}
    	$this->view->assign('page_title', $this->head_title);
    }
    
    /**
     * 批量传入js
     * @param string $jsstr
     */
    public function setHeadJavascripts($jsstr, $version = null, $folder = 'js', $site = null) {
    	$jss = explode(',', $jsstr);
    	foreach ($jss as $js) {
    		$this->setHeadJavascript($js, $version, $folder, $site);
    	}
    }
    
    /**
     * 设置网页加载的javascript
     * 最终拼接结果：$site/[$folder/]$jsfile[?$version]
     * $site如果是空，则自动为static站点域名
     * @param string $jsfile
     * @param int $version
     * @param string $folder
     * @param string $site
     */
    public function setHeadJavascript($jsfile, $version = null, $folder = 'js', $site = null) {
    	$jsfile .= ".js";
    	if (!is_null($version)) {
    		$jsfile .= "?$version";
    	}
    	if (!is_null($folder)) {
    		$jsfile = $folder . '/' . $jsfile;
    	}
    	if (is_null($site)) {
    		$jsfile = Hi::ini('SITE_URL/static') . '/' . $jsfile;
    	} else {
    		$jsfile = $site . '/' . $jsfile;
    	}
    	$this->head_javascript[] = $jsfile;
    	$this->view->assign('page_javascripts', $this->head_javascript);
    	$page_javascript = "";
    	foreach ($this->head_javascript as $javascript) {
    		$page_javascript .= "<script type=\"text/javascript\" src=\"$javascript\"></script>\r\n";
    	}
    	$this->view->assign('page_javascript', $page_javascript);
    }
    
    /**
     * 用法参见setHeadJavascript
     * 最终拼接结果：$site/[$folder/]$cssfile[?$version}
     * @param string $cssfile
     * @param int $version
     * @param string $folder
     * @param string $site
     */
    public function setHeadCSS($cssfile, $version = null, $folder = 'styles', $site = null) {
    	$cssfile .= ".css";
    	if (!is_null($version)) {
    		$cssfile .= "?$version";
    	}
    	if (!is_null($folder)) {
    		$cssfile = $folder . '/' . $cssfile;
    	}
    	if (is_null($site)) {
    		$cssfile = Hi::ini('SITE_URL/static') . '/' . $cssfile;
    	} else {
    		$cssfile = $site . '/' . $cssfile;
    	}
    	$this->head_css[] = $cssfile;
    	$this->view->assign('page_csses', $this->head_css);
    	$page_css = "";
    	foreach ($this->head_css as $css) {
    		$page_css .= "<link href=\"$css\" rel=\"stylesheet\" type=\"text/css\" />\r\n";
    	}
    	$this->view->assign('page_css', $page_css);
    }
    
    public function setHeadCSSAll($cssfiles,$folder = 'css', $site = null){
    	$csslist = explode(",",$cssfiles);
    	$ext = ".css";
    	
    	if (is_null($site)) {
    		$site = Hi::ini('SITE_URL/static');
    	}
    	
    	$page_cssall = "";
    	foreach($csslist as $css){
    		$css = $site."/".$folder."/".$css.$ext;
    		$page_cssall .= "<link href=\"$css\" rel=\"stylesheet\" type=\"text/css\" />\r\n";
    	}
    	$this->view->assign('page_cssall', $page_cssall);
    	
    }

    function initialize() {

    }

    function __destruct() {
        if (Hi::ini('db_autocommit'))
            Hi::getDb()->commit();
    }

    function dbstarttrans() {
        Hi::getDb()->starttransaction();
    }

    function dbcommit() {
        Hi::getDb()->commit();
    }

    function dbrollback() {
        Hi::getDb()->rollback();
    }

    function BaseController(& $get, & $post, $model_root=NULL, $view_root=NULL, $obj_root=NULL) {

        $this->time = time();
        $this->ip = util::getip();
        $this->get = & $get;
        $this->post = & $post;
        $this->model_root = $model_root;
        $this->view_root = $view_root;
        $this->obj_root = $obj_root;

        $this->http_domain = $_SERVER['HTTP_HOST'];
        $this->http_prefix = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $tmp_http_prefix = explode('/', $this->http_prefix);
        $this->http_prefix = "";
        foreach ($tmp_http_prefix as $hp) {
            if ($hp == $tmp_http_prefix[count($tmp_http_prefix) - 1])
                continue;
            $this->http_prefix .= $hp . '/';
        }
        $this->http_prefix = 'http://' . $this->http_prefix;

        # 打开数据库连接
        // $this->init_db();
	if (!Hi::ini('db_disable')) {
	    $this->db = Hi::getDb();
	}
        $this->init_template();
    }

    function init_template() {
        @$style = $this->hgetcookie('style');
        if (!isset($style)) {
            $style = Hi::ini('style_name'); //$this->setting['style_name'];
        }
        if ($style == null)
            $style = "default";
        $tplpath = Hi::ini('tpldir_path');
        if (!empty($this->view_root)) {
            $tplpath = $this->view_root;
        }

        $objpath = Hi::ini('objdir_path');
        if (!empty($this->obj_root)) {
            $objpath = $this->obj_root;
        }


        $this->view = new HiTemplate($style, $tplpath, $objpath);
        //$this->view = new HiTemplate($style, defined("TPLDIR_PATH") ? TPLDIR_PATH : null, defined("OBJDIR_PATH") ? OBJDIR_PATH : null);
//		$this->view->setlang($this->setting['lang_name'],'front');
        //passport include
        /* $ppfile = HICMS_ROOT . '/data/passport.inc.php';
          if (file_exists($ppfile)) {
          include($ppfile);
          if (defined('PP_OPEN') && PP_OPEN) {
          $this->forward = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->setting['site_url'];
          if (PP_TYPE == 'client')
          $this->view->assign('pp_api', PP_API . PP_LOGIN);
          }
          } */

        //$this->view->assign('pluginlist', array_values($this->plugin));
        //$this->view->assign('channellist', $this->channel);
        //$this->view->assign('stylelist', $this->style);
        $this->view->assign('user', $this->user);

        $referer = empty($_SERVER["QUERY_STRING"]) ? '' : "-" . $this->setting['seo_prefix'] . $_SERVER["QUERY_STRING"];
        $this->view->assign('referer', urlencode($referer));
        $this->view->assign('timenow', $this->date($this->time, 3));
        //$this->view->assign('setting', $this->setting);
        //$this->view->assign('navtitle', '');

        $this->view->assign('style', $style);
        //@$hotsearch = unserialize($this->setting['hotsearch']);
        // $hotsearch = is_array($hotsearch) ? $hotsearch : array();
        // $this->view->assign('hotsearch', $hotsearch);

        //$this->view->assign('tpl', 'static/images/'.$style);
        $this->view->assign('urlbase', util::getBaseURL());
    }

    function load($model, $base = NULL) {

        $base = $base ? $base : $this;
        if (empty($_ENV[$model])) {
            $modelname = ucwords($model) . 'Model';
            if (!empty($this->model_root)) {
                require $this->model_root . "/$modelname.php";
            } else {
                throw new HiException('No model file of  "%s".', $modelname . 'php');
                //require APP_ROOT . "/model/$modelname.php";
            }

            $_ENV[$model] = new $modelname($this->db, $base);
        }
        return $_ENV[$model];
    }

    function message($message, $redirect = '', $type = 1) {
		$inajax = empty($this->get['inajax'])?empty($this->post['inajax'])?0:1:1;
		if($inajax) exit($message."<script>window.location.href='{$redirect}';</script>");

        $this->view->assign('page_title', '提示消息 '.Hi::ini("page_name"));
        $this->view->assign('message', $message);
        $this->view->assign('redirect', $redirect);
        if ($type == 0) {
            $this->view->display('message');
        } else if ($type == 1) {
            $this->view->display('message');
        } else {
            $this->view->assign('ajax', 1);
            $this->view->assign('charset', Hi::ini('response_charset'));
            $this->view->display('message');
        }
        exit;
    }

    public function error($errorid, $errormessage, $returnid = 0,$okmessage = '',$redirect = null) {
    	$error = array(
    		'errorid' => $errorid,
    		'errormessage' => $errormessage,
    		'okmessage' => $okmessage,
    		'returnid' => $returnid
    	);
    	if (!is_null($redirect)) $error['redirect'] = $redirect;
    	echo json_encode($error);
    	exit;
    }

    function ajaxmessage($message, $redirect = '', $type = 1) {
		//弹窗开关
		$inajax = empty($this->get['inajax'])?empty($this->post['inajax'])?0:1:1;

        $this->view->assign('message', $message);
        $this->view->assign('redirect', $redirect);
        if ($type == 0) {
            $this->view->display('message');
        } else if ($type == 1) {
            $this->view->display('message');
        } else {
            $this->view->assign('ajax', 1);
            $this->view->assign('charset', Hi::ini('response_charset'));
            $this->view->display('message');
        }
        exit;
    }

    function header($url='') {
        if (empty($url)) {
            header("Location:{$this->setting['site_url']}");
        } else {
            header("Location:{$this->setting['seo_prefix']}$url{$this->setting['seo_suffix']}");
        }
    }

    function date($time, $type = 3, $friendly=0) {
        $format[] = $type & 2 ? (!empty($this->setting['date_format']) ? $this->setting['date_format'] : 'Y-n-j') : '';
        $format[] = $type & 1 ? (!empty($this->setting['time_format']) ? $this->setting['time_format'] : 'H:i') : '';
        $timeoffset = $this->setting['time_offset'] * 3600 + $this->setting['time_diff'] * 60;
        $timestring = gmdate(implode(' ', $format), $time + $timeoffset);
        if ($friendly) {
            $dtime = $this->time - $time;
            $dday = intval(date('Ymd', $this->time)) - intval(date('Ymd', $time));
            $dyear = intval(date('Y', $this->time)) - intval(date('Y', $time));
            if ($dtime < 60) {
                $timestring = $dtime . $this->view->lang['beforeSeconds'];
            } elseif ($dtime < 3600) {
                $timestring = intval($dtime / 60) . $this->view->lang['beforeMinutes'];
            } elseif ($dtime >= 3600 && 0 == $dday) {
                $timestring = intval($dtime / 3600) . $this->view->lang['beforeHours'];
            }
        }
        return $timestring;
    }

    function hsetcookie($var, $value, $life = 0) {
        $domain = $this->setting['cookie_domain'] ? $this->setting['cookie_domain'] : '';
        $cookiepre = $this->setting['cookie_pre'] ? $this->setting['cookie_pre'] : 'hicms_';
        setcookie($cookiepre . $var, $value, $life ? $this->time + $life : 0, '/', $domain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
    }

    function hgetcookie($var) {
        $cookiepre = $this->setting['cookie_pre'] ? $this->setting['cookie_pre'] : 'hicms_';
        return $_COOKIE[$cookiepre . $var];
    }

    function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
        $ckey_length = 4;
        $key = md5($key ? $key : $this->setting['auth_key']);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    function permission() {
        /*
         * return array(
         *   "method1"=>true,
         *   "method2"=>"functionkey1",
         *   "method3"=>"functionkey2",
         *   "method4"=>"functionkey3",
         * );
         *
         */

        return array("all" => true); //不限制功能
    }

    function checkable($url) {
        return true;

        /* 标准的权限控制写法*
          if ($this->visitor->get('privilege') == null) {
          return false;
          }
          if ($this->visitor->get('privilege') == 'all') {
          return true;
          }

          $permconfig = $this->permission();

          if (array_key_exists("all", $permconfig)) {
          return $permconfig["all"];
          }

          $permission = new MyPermission(1, $this->visitor->get('privilege'));
          if (array_key_exists($action, $permconfig)) {
          if ($permconfig[$action] === true) {
          return true;
          } else if ($permconfig[$action] === false) {
          return false;
          } else {
          return $permission->checkbykey($permconfig[$action]);
          }
          }
          return $permission->checkbyentery($controller, $action);
         */
    }

    function multi($num, $perpage, $curpage, $mpmultiurl, $maxpages = 0, $page = 10, $autogoto = TRUE, $simple = FALSE) {
        global $maxpage;
        $multipage = '';
        $seo_prefix = $this->setting['seo_prefix'];
        $seo_suffix = $this->setting['seo_suffix'];
        $mpurl = $seo_prefix . $mpurl . '-';
        $realpages = 1;
        if ($num > $perpage) {
            $offset = 2;
            $realpages = @ceil($num / $perpage);
            $pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;
            if ($page > $pages) {
                $from = 1;
                $to = $pages;
            } else {
                $from = $curpage - $offset;
                $to = $from + $page - 1;
                if ($from < 1) {
                    $to = $curpage + 1 - $from;
                    $from = 1;
                    if ($to - $from < $page) {
                        $to = $page;
                    }
                } elseif ($to > $pages) {
                    $from = $pages - $page + 1;
                    $to = $pages;
                }
            }
            $multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="' . $mpurl . '1"' . $seo_suffix . ' >1 ...</a>' : '') .
                    ($curpage > 1 && !$simple ? '<a href="' . $mpurl . ($curpage - 1) . $seo_suffix . '" >&lsaquo;&lsaquo;</a>' : '');
            for ($i = $from; $i <= $to; $i++) {
                $multipage .= $i == $curpage ? '<span class="gray">' . $i . '</span>' : '<a href="' . $mpurl . $i . $seo_suffix . '" >' . $i . '</a>';
            }
            $multipage .= ( $curpage < $pages && !$simple ? '<a href="' . $mpurl . ($curpage + 1) . $seo_suffix . '" >&rsaquo;&rsaquo;</a>' : '') .
                    ($to < $pages ? '<a href="' . $mpurl . $pages . $seo_suffix . '" >... ' . $realpages . '</a>' : '') .
                    (!$simple && $pages > $page && !$ajaxtarget ? '<kbd><input type="text" name="custompage" size="3" onkeydown="if(event.keyCode==13) {window.location=\'' . $mpurl . '\'+this.value+\'' . $seo_suffix . '\'; return false;}" /></kbd>' : '');

            $multipage = $multipage ? (!$simple ? '<span class="gray">&nbsp;' . $this->view->lang['commonTotal'] . $num . $this->view->lang['commonTotalNum'] . '&nbsp;</span>' : '') . $multipage : '';
        }
        $maxpage = $realpages;
        return $multipage;
    }

    /**
     * fantom
     * 2010-07-06 add
     */
    //分页
    function BaseMulti($num, $perpage, $curpage, $mpurl, $page = 5, $type="survey") {
        //$page = 5;
        $multipage = '';
        $realpages = 1;
        if ($num > $perpage) {
            $offset = 2;
            $pages = @ceil($num / $perpage);
            if ($page > $pages) {
                $from = 1;
                $to = $pages;
            } else {
                $from = $curpage - $offset;
                $to = $from + $page - 1;
                if ($from < 1) {
                    $to = $curpage + 1 - $from;
                    $from = 1;
                    if ($to - $from < $page) {
                        $to = $page;
                    }
                } elseif ($to > $pages) {
                    $from = $pages - $page + 1;
                    $to = $pages;
                }
            }
            if ($type == "survey") {
                $multipage = ($curpage - $offset > 1 && $pages > $page ? "<a  onclick=\"getpage(1, '{$mpurl}');\" href=\"javascript:void(0);\" class=\"first\">1 ...</a>" : '') .
                        ($curpage > 1 ? "<a  onclick=\"getpage(" . ($curpage - 1) . ", '{$mpurl}');\" href=\"javascript:void(0);\"  class=\"prev\">上一页</a>" : '');
                for ($i = $from; $i <= $to; $i++) {
                    $multipage .= $i == $curpage ? '<a class="thisclass">' . $i . '</a>' :
                            "<a onclick=\"getpage({$i}, '{$mpurl}');\" href=\"javascript:void(0);\">{$i}</a>";
                }
                $multipage .= ( $curpage < $pages ? "<a onclick=\"getpage(" . ($curpage + 1) . ", '{$mpurl}');\" href=\"javascript:void(0);\" class=\"next\">下一页</a>" : '') .
                        (0 && $to < $pages ? "<a onclick=\"getpage({$pages}, '{$mpurl}');\" href=\"javascript:void(0);\" class=\"last\">... {$realpages}</a>" : '');
                $multipage = $multipage ? '<div class="survey_pages">' . '<a>&nbsp;共' . $num . '页&nbsp;</a>' . $multipage . '</div>' : '';
            } else {
                $multipage = ($curpage - $offset > 1 && $pages > $page ? "<a  onclick=\"getpage(1, '{$mpurl}');\" href=\"javascript:void(0);\"  class=\"first\">1 ...</a>" : '') .
                        ($curpage > 1 ? "<a  onclick=\"getpage(" . ($curpage - 1) . ", '{$mpurl}');\" href=\"javascript:void(0);\"  class=\"prev\">next</a>" : '');
                for ($i = $from; $i <= $to; $i++) {
                    $multipage .= $i == $curpage ? '<strong>' . $i . '</strong>' :
                            "<a onclick=\"getpage({$i}, '{$mpurl}');\" href=\"javascript:void(0);\">{$i}</a>";
                }
                $multipage .= ( $curpage < $pages ? "<a onclick=\"getpage(" . ($curpage + 1) . ", '{$mpurl}');\" href=\"javascript:void(0);\" class=\"next\">&rsaquo;&rsaquo;</a>" : '') .
                        (0 && $to < $pages ? "<a onclick=\"getpage({$pages}, '{$mpurl}');\" href=\"javascript:void(0);\" class=\"last\">... {$realpages}</a>" : '');
                $multipage = $multipage ? '<div class="pages">' . '<em>&nbsp;' . $num . '&nbsp;</em>' . $multipage . '</div>' : '';
            }
        }
        $maxpage = $realpages;
        return $multipage;
    }

    /*
     * 上传图片
     * $file        string  上传的原图片
     * $uploaddir   string  上传目录
     * $type        string  类型
     * $status      int     0：不生存缩略图 1：生成缩略图
     * $tow         int     缩略图宽
     * $toh         int     缩略图高
     */

    function upLoadPictrue($file, $uploaddir, $type, $status = 0, $tow = 100, $toh = 100) {

        $result = array();

        if ($_FILES[$file]['error'] != UPLOAD_ERR_NO_FILE) {

            $picfile = $_FILES[$file];
            $allow_ext = array('jpg', 'gif', 'bmp', 'png');
            $fileext = strtolower($this->fileext($picfile['name']));

            if (!in_array($fileext, $allow_ext)) {
                exit('只允许上传jpg,gif,bmp, png图片类文件');
            }

            if ($picfile['size'] >= 800000) {
                exit('你上传的文件大小超过了最大值800k');
            }
            if ($picfile['error'] == UPLOAD_ERR_OK) {

                $target = $this->checkdir($uploaddir, $type, $fileext);
                if (@copy($picfile['tmp_name'], $target) || (function_exists('move_uploaded_file') && @move_uploaded_file($picfile['tmp_name'], $target))) {
                    @unlink($picfile['tmp_name']);
                    $status && $this->makethumb($target, $tow, $toh);
                    $result = substr($target, strripos($target, '/') + 1);
                } else {
                    exit('图片上传失败');
                }
            }
        }
        return $result;
    }

    /*
     * 生成缩略图
     * $file    string  原图
     * $tow     int     缩略图宽
     * $toh     int     缩略图高
     */

    function makethumb($srcfile, $tow = 100, $toh= 100) {

        //判断文件是否存在
        if (!file_exists($srcfile)) {
            return '';
        }
        $dstfile = $srcfile . '.thumb.jpg';

        //缩略图大小
        if ($tow < 60)
            $tow = 60;
        if ($toh < 60)
            $toh = 60;

        $make_max = 0;
        $maxtow = 500;
        $maxtoh = 500;
        if ($maxtow >= 300 && $maxtoh >= 300) {
            $make_max = 1;
        }

        //获取图片信息
        $im = '';
        if ($data = getimagesize($srcfile)) {
            if ($data[2] == 1) {
                $make_max = 0; //gif不处理
                if (function_exists("imagecreatefromgif")) {
                    $im = imagecreatefromgif($srcfile);
                }
            } elseif ($data[2] == 2) {
                if (function_exists("imagecreatefromjpeg")) {
                    $im = imagecreatefromjpeg($srcfile);
                }
            } elseif ($data[2] == 3) {
                if (function_exists("imagecreatefrompng")) {
                    $im = imagecreatefrompng($srcfile);
                }
            }
        }
        if (!$im)
            return '';

        $srcw = imagesx($im);
        $srch = imagesy($im);

        $towh = $tow / $toh;
        $srcwh = $srcw / $srch;
        if ($towh <= $srcwh) {
            $ftow = $tow;
            $ftoh = $ftow * ($srch / $srcw);

            $fmaxtow = $maxtow;
            $fmaxtoh = $fmaxtow * ($srch / $srcw);
        } else {
            $ftoh = $toh;
            $ftow = $ftoh * ($srcw / $srch);

            $fmaxtoh = $maxtoh;
            $fmaxtow = $fmaxtoh * ($srcw / $srch);
        }
        if ($srcw <= $maxtow && $srch <= $maxtoh) {
            $make_max = 0; //不处理
        }
        if ($srcw > $tow || $srch > $toh) {
            if (function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") && @$ni = imagecreatetruecolor($ftow, $ftoh)) {
                imagecopyresampled($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);
                //大图片
                if ($make_max && @$maxni = imagecreatetruecolor($fmaxtow, $fmaxtoh)) {
                    imagecopyresampled($maxni, $im, 0, 0, 0, 0, $fmaxtow, $fmaxtoh, $srcw, $srch);
                }
            } elseif (function_exists("imagecreate") && function_exists("imagecopyresized") && @$ni = imagecreate($ftow, $ftoh)) {
                imagecopyresized($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);
                //大图片
                if ($make_max && @$maxni = imagecreate($fmaxtow, $fmaxtoh)) {
                    imagecopyresized($maxni, $im, 0, 0, 0, 0, $fmaxtow, $fmaxtoh, $srcw, $srch);
                }
            } else {
                return '';
            }
            if (function_exists('imagejpeg')) {
                imagejpeg($ni, $dstfile, 90);
                //大图片
                if ($make_max) {
                    imagejpeg($maxni, $srcfile, 90);
                }
            } elseif (function_exists('imagepng')) {
                imagepng($ni, $dstfile);
                //大图片
                if ($make_max) {
                    imagepng($maxni, $srcfile);
                }
            }
            imagedestroy($ni);
            if ($make_max) {
                imagedestroy($maxni);
            }
        }
        imagedestroy($im);

        if (!file_exists($dstfile)) {
            return '';
        } else {
            return $dstfile;
        }
    }

    /*
     * 判断文件目录是否存在，不存在则创建
     * $dir    string  文件目录
     * $type   string   类型
     * $filename   string  文件后缀
     */

    function checkdir($dir, $type, $filename) {
        if (is_dir($dir)) {
            if (!$fp = @fopen("{$dir}/test.txt", 'w')) {
                exit("{$dir}目录不可写，请修改权限");
            } else {
                @fclose($fp);
                @unlink("{$dir}/test.txt");

                if (is_dir("{$dir}/{$type}")) {
                    if (!$fp = @fopen("{$dir}/{$type}/test.txt", 'w')) {
                        exit('目录不可写，请修改权限');
                    } else {
                        @fclose($fp);
                        @unlink("{$dir}/{$type}/test.txt");
                    }
                } else {
                    if (false == mkdir("{$dir}/{$type}")) {
                        exit("创建目录{$dir}/{$type}失败");
                    }
                }
            }
        } else {
            if (false == mkdir($dir)) {
                exit("创建目录{$dir}失败");
            } else {
                $this->checkdir($dir, $type, $filename);
            }
        }
        return "{$dir}/{$type}/" . $this->random(5) . '.' . $filename;
    }

    //获取文件名后缀
    protected function fileext($filename) {
        return strtolower(trim(substr(strrchr($filename, '.'), 1)));
    }

    //产生随机字符
    function random($length, $numeric = 0) {
        PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
        if ($numeric) {
            $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $max = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }

    function redirect($controller, $action = 'default', array $args=array(), $path='') {
        $url = util::makeurl($controller, $action, $args);
        header("Location:{$path}$url");
    }

    /*
      添加指定form指定字段的校验规则
      $this->->addValidations(new ValidationRule('max_length', 5, '不能超过5个字符'),'form1','field1');
      或
      $this->->addValidations(V('max_length', 5, '不能超过5个字符'),'form1','field1');

      如果要一次性添加多个验证规则，需要使用二维数组：

      $this->addValidations(array(
      V('min', 3, '不能小于3'),
      V('max', 9, '不能大于9'),
      ),'form1','field1');

      如果要添加一个 callback 方法作为验证规则，必须这样写：
      $this->addValidations(V(array($obj, 'method_name'), $args, 'error_message'),'form1','field1');

      添加一个Model中现存的校验规则，必须这样写：
      $this->addValidations(M('AdminUser'),'form1','field1');
     */

    function addvalidations($validations, $field=null, $form=null) {
        if ($validations instanceof ValidationRule) {
            $this->_addvalidation($validations, $field, $form);
        } elseif ($validations instanceof BaseModel) {
            if (empty($field)) {
                foreach ($validations->getvalidation() as $key => $v) {
                    $this->_addvalidation($v, $key, $form);
                }
            } else {
                $this->_addvalidation($validations->getvalidation($field), $field, $form);
            }
        } elseif (is_array($validations)) {
            foreach ($validations as $v) {
                $this->_addvalidation($v, $field, $form);
            }
        } else {

            throw new HiException("args type of  validations must be array or be BaseModel or beValidationRule ");
        }
    }

    protected function _addvalidation(ValidationRule $validation, $field, $form=null) {
        if (empty($form))
            $form = "#";
        if (!array_key_exists($form, $this->_validations))
            $this->_validations[$form] = array();
        if (!array_key_exists($field, $this->_validations[$form]))
            $this->_validations[$form][$field] = array();
        $this->_validations[$form][$field][] = $validation;
    }

    //校验指定form的数据
    function validate($data, & $failed = null, $form=null) {
        if (empty($form))
            $form = "#";

        if (count($this->_validations) > 0) {

            $_failed = array();
            foreach ($data as $key => $v) {

                if (!$this->validatefield($v, $form, $key, $_failed, $data))
                    $this->_is_valid = false;
            }
            //设置返回错误信息
            $this->setfailedhtml($_failed);
        } else {
            $this->_failedhtml = array();
            $this->_is_valid = true;
        }
        //

        return $this->_is_valid;
    }

    protected function setfailedhtml($_failed) {


        $this->_failedhtml = array();

        foreach ($_failed as $field => $errs) {

            $str = "<font color=\"red\">";
            foreach ($errs as $r) {
                $str.=$r->msg . "。";
            }
            $str.= "</font>";
            $this->_failedhtml[$field] = $str;
        }
    }

    function getfailedhtml() {
        return $this->_failedhtml;
    }

    //校验指定form指定字段数据的数据
    protected function validatefield($value, $form, $field, &$_failed, $data) {
        if (empty($form))
            $form = "#";
        $failed = array();
        $is_valid = false;
        if (array_key_exists($form, $this->_validations) && array_key_exists($field, $this->_validations[$form])) {

            //TODO 如果是equlto校验，同时 规则以#开头 需要替换$v的值
            //TODO 如果是ajax校验，则需要调用ajax调用的函数进行校验

            $is_valid = (bool) Validator::validateBatch($value, $this->_validations[$form][$field], Validator::CHECK_ALL, $failed);
            if (!$is_valid) {
                foreach ($failed as $value) {
                    if (!array_key_exists($field, $_failed))
                        $_failed[$field] = array();
                    $_failed[$field][] = $value;
                }
            }
        }else {
            $is_valid = true;
        }

        return $is_valid;
    }

    function getvalidatejs($form=null) {
        return $this->getvalidatejsrule() . "\r\n ,\r\n" . $this->getvalidatejsmsg();
    }

    //获得指定form的js校验规则
function getvalidatejsrule($form=null) {

        if (empty($form))
            $form = "#";

        $rules = "rules: {\r\n";
        $msg = "";

        if (count($this->_validations) > 0) {
            $fieldarray = array();
            foreach ($this->_validations[$form] as $field => $value) {
                //$rules.=$field . ":{\r\n";
                $field_rules = $field . ":{\r\n";
                $rulerray = array();

                foreach ($value as $vrule) {
                    switch ($vrule->rule) {
                        case "not_empty":
                            $rulerray[] = "required: true";
                            break;
                        case "min":
                            $rulerray[] = "min: " . $vrule->args[0] . "";
                            break;
                        case "max":
                            $rulerray[] = "max: " . $vrule->args[0] . "";
                            break;
                        case "min_length":
                            $rulerray[] = "minlength: " . $vrule->args[0] . "";
                            break;
                        case "max_length":
                            $rulerray[] = "maxlength: " . $vrule->args[0] . "";
                            break;
                        case "is_email":
                            $rulerray[] = "email: true";
                            break;
                        case "equal":
                            if (strpos($vrule->args[0], "#") !== FALSE) {
                                $rulerray[] = "equalTo: \"" . $vrule->args[0] . "\"";
                            }
                            break;
                        case "is_date":
                            $rulerray[] = "date: true";
                            break;
                        case "is_digits":
                            $rulerray[] = "digits: true";
                            break;
                        case "between":
                            $rulerray[] = " range:[" . $vrule->args[0] . "," . $vrule->args[1] . "]";
                            break;

                        case "ajax":
                            if (!empty($vrule->args[1]) && $vrule->args[1] == 'url') {
                                $rulerray[] = " remote:\"" . $vrule->args[0] . "\"";
                            } else {
                                $rulerray[] = " remote:\"" . util::makeurl($this->str_controller, $vrule->args[0]) . "\"";
                            }

                            break;
                    }
                }
                //$rules.="},\r\n";
                $field_rules.=implode(",\r\n", $rulerray);
                $field_rules.="\r\n";
                $field_rules.="}"; //,\r\n
                $fieldarray[] = $field_rules;
            }

            $rules.=implode(",\r\n", $fieldarray);
            $rules.="\r\n";
        }
        $rules.="}\r\n";
        return $rules;
    }

    //返回指定form的校验消息
	function getvalidatejsmsg($form=null) {

        if (empty($form))
            $form = "#";

        $rules = "messages: {\r\n";
        $msg = "";

        if (count($this->_validations) > 0) {
            $fieldarray = array();
            foreach ($this->_validations[$form] as $field => $value) {

                $field_rules = $field . ":{\r\n";

                $rulerray = array();
                foreach ($value as $vrule) {
                    switch ($vrule->rule) {
                        case "not_empty":
                            $rulerray[] = "required: \"" . $vrule->msg . "\"";
                            break;
                        case "min":
                            $rulerray[] = "min: \"" . $vrule->msg . "\"";
                            break;
                        case "max":
                            $rulerray[] = "max:  \"" . $vrule->msg . "\"";
                            break;
                        case "min_length":
                            $rulerray[] = "minlength:  \"" . $vrule->msg . "\"";
                            break;
                        case "max_length":
                            $rulerray[] = "maxlength:  \"" . $vrule->msg . "\"";
                            break;
                        case "is_email":
                            $rulerray[] = "email: \"" . $vrule->msg . "\"";
                            break;
                        case "equal":
                            if (strpos($vrule->args[0], "#") !== FALSE) {
                                $rulerray[] = "equalTo:  \"" . $vrule->msg . "\"";
                            }
                            break;
                        case "is_date":
                            $rulerray[] = "date: \"" . $vrule->msg . "\"";
                            break;
                        case "is_digits":
                            $rulerray[] = "digits:  \"" . $vrule->msg . "\"";
                            break;
                        case "between":
                            $rulerray[] = " range: \"" . $vrule->msg . "\"";
                            break;
                        case "ajax":
                            $rulerray[] = " remote: \"" . $vrule->msg . "\"";
                            break;
                    }
                }
                $field_rules.=implode(",\r\n", $rulerray);
                $field_rules.="\r\n";
                $field_rules.="}"; //,\r\n
                $fieldarray[] = $field_rules;
            }
            $rules.=implode(",\r\n", $fieldarray);
            $rules.="\r\n";
        }
        $rules.="}\r\n";
        return $rules;
    }

    //校验指定form指定字段的数据，并以ajax json 方式返回
    function ajaxvalidate($value, $form, $field) {
        if (empty($form))
            $form = "#";
    }

    //默认ajax校验处理方法
    function dovalidate() {

    }

    //判断提交是否正确
    function submitcheck($var) {

        // preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", //$_SERVER['HTTP_HOST'])) &&
        if (!empty($this->post[$var]) && $_SERVER['REQUEST_METHOD'] == 'POST') {
            return true;
            /*
              if (empty($_SERVER['HTTP_REFERER']) || $_POST['formhash'] == $this->formhash()) {
              return true;
              } else {
              // showmessage('submit_invalid');
              $this->message('submit_invalid');
              }

             */
        } else {
            return false;
        }
    }

    //产生form防伪码
    function formhash() {
        global $_SGLOBAL, $_SCONFIG;

        if (empty($_SGLOBAL['formhash'])) {
            $hashadd = defined('IN_ADMINCP') ? 'Only For UCenter Home AdminCP' : '';
            $_SGLOBAL['formhash'] = substr(md5(substr($_SGLOBAL['timestamp'], 0, -7) . '|' . $_SGLOBAL['supe_uid'] . '|' . md5($_SCONFIG['sitekey']) . '|' . $hashadd), 8, 8);
        }
        return $_SGLOBAL['formhash'];
    }

    function setcookieurl($key=null, $url=null) {

        if (empty($key)

            )$key = "cookieurl_" . get_class($this);

        if (empty($url))
            $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $this->hsetcookie($key, $url, 24 * 3600 * 365);
    }

    function getcookieurl($defaulturl, $key=null, $isdelete=true) {

        if (empty($key)

            )$key = "cookieurl_" . get_class($this);

        $url = $this->hgetcookie($key);
        if (empty($url))
            $url = $defaulturl;
        if ($isdelete)
            $this->hsetcookie($key, FALSE, 0); //用过就删除
        return $url;
    }

    function getcururl() {

        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $url;
    }

    function ispost() {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return true;
        } else {
            return false;
        }
    }

    public function toBase() {
	$base = new BaseController($this->get, $this->post);
	$base->str_controller = $this->str_controller;
	$base->str_action = $this->str_action;
	return $base;
    }

}
