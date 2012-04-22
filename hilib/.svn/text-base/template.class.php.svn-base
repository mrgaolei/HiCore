<?php
!defined('HICORE_PATH') && exit('Access Denied');
class HiTemplate {

    var $tplname;
    var $tplnamedefault;
    var $tpldir;
    var $objdir;
    var $tplfile;
    var $objfile;
    var $vars = array();
    var $force = 0;
    var $var_regexp = "\@?\\\$[a-z_][\\\$\w]*(?:\[[\w\-\.\"\'\[\]\$]+\])*";
    var $vtag_regexp = "\<\?=(\@?\\\$[a-zA-Z_][\\\$\w]*(?:\[[\w\-\.\"\'\[\]\$]+\])*)\?\>";
    var $const_regexp = "\{([\w]+)\}";
    var $lang = array();

    var $isreturnhtml=false;
    var $fileprefix=null; //obj文件名的前缀

    public function __construct($tplname='default', $tpldir = null, $objdir = null,$isreturnhtml=false,$fileprefix=null) {
        $this->HiTemplate($tplname, $tpldir, $objdir,$isreturnhtml,$fileprefix);
    }

    function HiTemplate($tplname='default', $tpldir = null, $objdir = null,$isreturnhtml=false,$fileprefix=null) {

         $this->tplname = $tplname;
         $this->tplnamedefault = $tplname;
       // $this->tplname = ($tplname !== 'default' && is_dir($tpldir . '/' . $tplname)) ? $tplname : 'default';
        if (is_null($tpldir)) {
        	throw new HiException('you must config parameter tpldir! ');
            //$this->tpldir = APP_ROOT . '/view/' . $this->tplname;
        } else {
            $this->tpldir = $tpldir;// . '/' . $tplname
        }
        if (is_null($objdir)) {
        	throw new HiException('you must config parameter objdir! ');
            //$this->objdir = CACHE_PATH . '/view';
        } else {
            $this->objdir = $objdir;
        }
        $this->isreturnhtml=$isreturnhtml;
        $this->fileprefix=$fileprefix;
    }
    
    function setTplname($tplname) {
    	$this->tplname = $tplname;
    }
    
    function shareDisplay($file, $tplname = 'share', $ajax = false, $isreturnhtml=false, $toxml = true,$tplpath=null) {
    	$this->setTplname($tplname);
    	$this->display($file, $ajax, $isreturnhtml, $toxml, $tplpath);
    }

    function assign($k, $v) {
        $this->vars[$k] = $v;
    }
    /*
    function setlang($langtype='zh', $filename) {
        include APP_ROOT . '/lang/' . $langtype . '/' . $filename . '.php';
        $this->lang = &$lang;
    }
    */

    function display($file, $ajax = false, $isreturnhtml=false, $toxml = true,$tplpath=null) {
        GLOBAL $starttime, $mquerynum;
        $mtime = explode(' ', microtime());
        $this->assign('runtime', number_format($mtime[1] + $mtime[0] - $starttime, 6));
        $this->assign('querynum', $mquerynum);
        extract($this->vars, EXTR_SKIP);

        if ($isreturnhtml||$this->isreturnhtml) {
            ob_start();
            include $this->gettpl($file, $ajax, $toxml,$tplpath);
            $tplhtml = ob_get_contents();
            ob_clean();
            return $tplhtml;
        } else {
            include $this->gettpl($file, $ajax, $toxml,$tplpath);
        }
    }

    function gettpl($file, $ajax = false, $toxml = true,$tplpath=null) {
        if (substr($file, 0, 7) == "file://") {
            $ppos = strrpos($file, "/");
            $dir_name = explode('/', substr($file, 7));
            $this->tplfile = APP_ROOT . "/" . substr($file, 7) . '.html';
            $this->objfile = $this->objdir . '/' . $dir_name[1] . '_' . substr($file, $ppos + 1) . '.tpl.php';
        } else {
            $tplpath=$tplpath==null?$this->tpldir:$tplpath;
            $objfile=$file;
            if(!empty($this->fileprefix)){
                 $objfile=$this->fileprefix."_".$file;
            }
            if(empty($this->tplname)){
                $this->tplfile = $tplpath . '/' . $file . '.html';
                $this->objfile = $this->objdir . '/' . $objfile . '.tpl.php';
            }else{
                $this->tplfile = $tplpath . '/'.$this->tplname.'/' . $file . '.html';
                $this->objfile = $this->objdir . '/'  .$this->tplname . "_" . $objfile . '.tpl.php';
                
            }
            
            /*if ($this->tplname !== 'default' && is_file($this->tpldir . '/' . $file . '.html')) {
                $this->tplfile = $this->tpldir . '/' . $file . '.html';
                $this->objfile = $this->objdir . '/' . $this->tplname . "_" . $file . '.tpl.php';
            } else {
                $this->tplfile = $this->tpldir . '/default/' . $file . '.html';
                $this->objfile = $this->objdir . '/' . $file . '.tpl.php';
            }*/

            
        }
        if (!file_exists($this->tplfile)) die('Template <u>' . $this->tplfile . '</u> not found !');
        if ($this->force || @filemtime($this->objfile) < @filemtime($this->tplfile)) {
            $this->complie($ajax, $toxml);
        }
        return $this->objfile;
    }

    function complie($ajax = false, $toxml = true) {
        $template = file::readfromfile($this->tplfile);
        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
        $template = preg_replace("/\{lang.(\w+?)\}/ise", "\$this->lang('\\1')", $template);
        if ('1' == $this->vars['setting']['seo_type'] && '1' == $this->vars['setting']['seo_type_doc']) {
            $template = preg_replace("/\{url.doc\-view\-(.+?)\['did'\]\}/ise", "\$this->stripvtag('{url doc-view-{eval echo urlencode(\\1[\'rawtitle\']);}}')", $template);
        }
        $template = preg_replace("/\{($this->var_regexp)\}/", "<?=\\1?>", $template);
        $template = preg_replace("/\{($this->const_regexp)\}/", "<?=\\1?>", $template);
        $template = preg_replace("/(?<!\<\?\=|\\\\)$this->var_regexp/", "<?=\\0?>", $template);
        $template = preg_replace("/\{\{eval (.*?)\}\}/ies", "\$this->stripvtag('<? \\1?>')", $template);
        $template = preg_replace("/\{eval (.*?)\}/ies", "\$this->stripvtag('<? \\1?>')", $template);
        $template = preg_replace("/\{mkurl\s+(.*?)\}/ies", "\$this->mkurl('<? \\1?>')", $template);
        $template = preg_replace("/\{userface\s+(\w*?)\s+(\w*?)\}/ies", "\$this->userface(\\1, \\2)", $template);
        $template = preg_replace("/\{url\s+(.*?)\}/ies", "\$this->url('<? \\1?>')", $template);
        $template = preg_replace("/\{for (.*?)\}/ies", "\$this->stripvtag('<? for(\\1) {?>')", $template);
        $template = preg_replace("/\{elseif\s+(.+?)\}/ies", "\$this->stripvtag('<? } elseif(\\1) { ?>')", $template);
        $template = preg_replace("/\{hdwiki:([^\}]+?)\/\}/ies", "\$this->hdwiki('\\1')", $template);
        for ($i = 0; $i < 3; $i++) {
            $template = preg_replace("/\{hdwiki:(.+?)\}(.+?)\{\/hdwiki\}/ies", "\$this->hdwiki('\\1', '\\2')", $template);
            $template = preg_replace("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/ies", "\$this->loopsection('\\1', '\\2', '\\3', '\\4')", $template);
            $template = preg_replace("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/ies", "\$this->loopsection('\\1', '', '\\2', '\\3')", $template);
        }
        $template = preg_replace("/\{if\s+(.+?)\}/ies", "\$this->stripvtag('<? if(\\1) { ?>')", $template);
        $template = preg_replace("/\{template\s+(\w+?)\}/is", "<? include \$this->gettpl('\\1');?>", $template);
        $template = preg_replace("/\{template\s+(.+?)\}/ise", "\$this->stripvtag('<? include \$this->gettpl(\\1); ?>')", $template);
        $template = preg_replace("/\{sharetemplate\s+(\w+?)\s+(\w+?)\}/is", "<? \$this->setTplname('\\2'); ?><? include \$this->gettpl('\\1');?><? \$this->setTplname(\$this->tplnamedefault); ?>", $template);
        $template = preg_replace("/\{sharetemplate\s+(.+?)\s+(.+?)\}/ise", "\$this->setTplname('\\2');\$this->stripvtag('<? include \$this->gettpl(\\1); ?>');\$this->setTplname(\$this->tplnamedefault);", $template);
        $template = preg_replace("/\{else\}/is", "<? } else { ?>", $template);
        $template = preg_replace("/\{\/if\}/is", "<? } ?>", $template);
        $template = preg_replace("/\{\/for\}/is", "<? } ?>", $template);
        $template = preg_replace("/$this->const_regexp/", "<?=\\1?>", $template);
        if ($ajax && $toxml) {
            $template = "<? echo '<?xml version=\"1.0\" encoding=\"" . Hi::ini('response_charset') . "\"?>';?>
\r\n<root><![CDATA[$template]]></root>";
        }
        $template = "<? if(!defined('IN_APP')) exit('Access Denied');?>\r\n$template";
        if ($ajax) {
            $template = "<? ob_end_clean();ob_start();
@header(\"Expires: -1\");
@header(\"Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0\", FALSE);
@header(\"Pragma: no-cache\");
@header(\"Content-type: text/html;charset=".Hi::ini('response_charset')."\");
@header(\"Content-type: application/xml; charset=" . Hi::ini('response_charset') . "\");?>
$template";
        }
        $template = preg_replace("/(\\\$[a-zA-Z_]\w+\[)([a-zA-Z_]\w+)\]/i", "\\1'\\2']", $template);
        $template = preg_replace("/\{url.(.+?)\}/ise", "\$this->url('\\1')", $template);
        $fp = fopen($this->objfile, 'w');
        fwrite($fp, $template);
        fclose($fp);
    }

    function stripvtag($s) {
        return preg_replace("/$this->vtag_regexp/is", "\\1", str_replace("\\\"", '"', $s));
    }
    
    function userface($uid, $size = 2) {
    	if (!in_array($size, array(1,2,3))) {
    		$size = 2;
    	}
    	$hash = substr(md5($uid), 0, 2);
    	$html = "<img src=\"http://".FACE_DOMAIN."/face/$hash/$uid/$size.jpg\" onerror=\"this.src='http://".FACE_DOMAIN."/static/face.jpg'\" />";
    	return $html;
    }

    function mkurl($u) {
        $u = str_replace('<?=', '', $u);
        $u = str_replace('<?', '', $u);
        $u = str_replace('?>', '', $u);
        $u = "<?=util::makeurl($u)?>";
        return $u;
    }

    function url($u) {
        $u = str_replace("'", "", $u);
        $u = str_replace("\"", "", $u);
        $u = str_replace('<?=', '', $u);
        $u = str_replace('<?', '', $u);
        $u = str_replace('?>', '', $u);
        $u = trim($u);
        $us = explode('/', $u);
        $args = "";
        if ($us[0]) {
            $args .= $us[0];
        }
        if ($us[1]) {
            $args .= ", ".$us[1];
        }
        if ($us[2]) {
            $args .= ", array(";
            for ($i = 2; $i < count($us); $i = $i + 2) {
                $args .= $us[$i]." => ".$us[$i+1].",";
            }
            $args .= ")";
        }
        $u = "<?=util::makeurl($args)?>";
        return $u;
    }

    function loopsection($arr, $k, $v, $statement) {
        $arr = $this->stripvtag($arr);
        $k = $this->stripvtag($k);
        $v = $this->stripvtag($v);
        $statement = str_replace("\\\"", '"', $statement);
        return $k ? "<? foreach((array)$arr as $k=>$v) {?>$statement<?}?>" : "<? foreach((array)$arr as $v) {?>$statement<? } ?>";
    }

    function lang($k) {
        return!empty($this->lang[$k]) ? $this->lang[$k] : "{ $k }";
    }

    function ___url($u) {
        if ('1' == $this->vars['setting']['seo_type'] && '1' == $this->vars['setting']['seo_type_doc'] && 'doc-view-' == substr($u, 0, 9)) {
            return "wiki/" . substr($u, 9);
        } else {
            return $this->vars['setting']['seo_prefix'] . $u . $this->vars['setting']['seo_suffix'];
        }
    }
/*
    function hdwiki($taglist, $statement='') {
        $tag = preg_split("/\s+/", trim($taglist));
        $taglist = str_replace("'", "\'", $taglist);
        if ('' != $statement) {
            $statement = str_replace("\\\"", '"', $statement);
            $statement = preg_replace_callback("/\[field:([^\]]+?)\/\]/is", array($this, 'callback'), $statement);
            return "<?foreach((array)\$_ENV['tag']->$tag[0]('$taglist') as \$data) {?>$statement<? } ?>";
        } else {
            return "<?echo \$_ENV['tag']->$tag[0]('$taglist');?>";
        }
    }
 * */


    function callback($matches) {
        $cmd = trim($matches[1]);
        $firstspace = strpos($cmd, ' ');
        if (!$firstspace) {
            return '<?=$data[' . $cmd . ']?>';
        } else {
            $field = substr($cmd, 0, $firstspace);
            $func = substr($cmd, $firstspace);
            return '<? echo ' . str_replace('@me', '$data[' . $field . ']', $func) . " ?>";
        }
    }

}
?>