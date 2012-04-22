<?php
!defined('HICORE_PATH') && exit('Access Denied');

class Helper_ImgCode
{
    /**
     * 验证码的配置
     *
     * @var array
     */
    static private $_config = array(
        // 用什么键名在 session 中保存验证码
        'imgcode_session_key' => '_IMGCODE',

        // 从 session 中读取的验证码
        'imgcode_session_value' => null,

        // 用什么键名在 session 中保存验证码过期时间
        'expired_session_key' => '_IMGCODE_EXPIRED',

        // 验证码过期时间
        'expired_session_value' => null,
    );

	/**
	 * 利用 GD 库产生验证码图像，并输出
     *
     * $style 参数值不同时，$options 参数包含的选项也不同。
     *
     * 用法：
     * @code php
     * // 控制器文件
     * class Controller_Default
     * {
     *     function doImgcode()
     *     {
     *         // 在控制器中用下列代码返回一个图像验证码
     *         return Helper_ImgCode::create(6, 900, 'simple');
     *     }
     * }
     *
     * // 模板文件中使用下列代码引用一个图像验证码
     * <img src="<?php echo url('default/imgcode'); ?>" border="0" />
     * @endcode
     *
     * @param int $length 验证码的长度
     * @param int $lefttime 验证码的有效期
     * @param string $style 验证码的样式
     * @param array $options 具体验证码样式的选项
     *
     * @return QView_Output 包含验证码图像的输出对象
	 */
    static function create(array $options = null,$lefttime = 900 )
    {
        //$class_name = 'Helper_ImgCode' . ucfirst(strtolower(preg_replace('/[^a-z0-9_]+/i', '', $style)));
        //$options = (array)$options;
       // $options['code_length'] = $length;
        //$imgcode_obj = new $class_name($options);

        $imgcode_obj = new seccode($options);

        $code = $imgcode_obj->generateCode();
        self::_writeImgcodeToSession($code, $lefttime);
        return $imgcode_obj->generateImage($code);
    }

    /**
     * 比较输入的验证码是否和 session 中保存的验证码一致（不区分大小写）
     *
     * 用法：
     * @code php
     * // 控制器文件
     * class Controller_Default
     * {
     *     function actionLogin()
     *     {
     *         if (Helper_ImgCode::isValid($this->_context->imgcode))
     *         {
     *             .... 比对通过
     *         }
     *
     *         ....
     *     }
     * }
     * @endcode
     *
     * @param string $code 要比对的验证码
     * @param boolean $clean_session 是否在比对通过后清理 session 中保存的验证码
     * @param boolean $case_sensitive 是否区分大小写
     *
     * @return boolean 比对结果
     */
    static function isValid($code, $clean_session = false, $case_sensitive = false)
    {
        $code_in_session = self::_readImgcodeFromSession();

        if (strlen($code_in_session) == 0 || strlen($code) == 0)
        {
            return false;
        }
        if ($case_sensitive)
        {
            $ret = (string)$code_in_session == (string)$code;
        }
        else
        {
            $ret = strtolower($code_in_session) == strtolower($code);
        }

        if ($ret && $clean_session)
        {
            self::_cleanImgcodeFromSession();
        }

        return $ret;
    }

	/**
     * 清除 session 中的验证码信息
	 */
    static function clean()
    {
        self::_cleanImgcodeFromSession();
    }

	

    /**
     * 写入验证码和验证码过期时间到 session
     *
     * @param string $code 要写入 session 的验证码
     * @param int $lefttime 验证码的有效期
     */
    private static function _writeImgcodeToSession($code, $lefttime)
    {
        if (isset($_SESSION))
        {
            $_SESSION[self::$_config['imgcode_session_key']] = $code;
            $_SESSION[self::$_config['expired_session_key']] = CURRENT_TIMESTAMP + intval($lefttime);
        }
    }

    /**
     * 从 session 取得验证码和验证码过期时间
     */
    private static function _readImgcodeFromSession()
    {
        if (!isset($_SESSION))
        {
            return false;
        }

        $key = self::$_config['imgcode_session_key'];
        $imgcode = isset($_SESSION[$key]) ? $_SESSION[$key] : '';
        $key = self::$_config['expired_session_key'];
        $expired = isset($_SESSION[$key]) ? $_SESSION[$key] : 0;

        if (CURRENT_TIMESTAMP >= $expired) return false;
        return $imgcode;
    }

    /**
     * 从 session 中清除验证码和验证码过期时间
     */
    private static function _cleanImgcodeFromSession()
    {
        if (isset($_SESSION))
        {
            $key = self::$_config['imgcode_session_key'];
            unset($_SESSION[$key]);
            $key = self::$_config['expired_session_key'];
            unset($_SESSION[$key]);
        }
    }
}

class seccode {

	var $code;			//note 100000-999999 范围内随机
	var $type 	= 0;		//note 0 英文图片验证码  1 中文图片验证码  2 Flash 验证码  3 语音验证码
	var $width 	= 150;		//note 宽度
	var $height 	= 60;		//note 高度
	var $background	= 1;		//note 随机图片背景
	var $adulterate	= 1;		//note 随机背景图形
	var $ttf 	= 0;		//note 随机 TTF 字体
	var $angle 	= 0;		//note 随机倾斜度
	var $color 	= 1;		//note 随机颜色
	var $size 	= 0;		//note 随机大小
	var $shadow 	= 1;		//note 文字阴影
	var $animator 	= 0;		//note /GIF 动画
	var $fontpath	= '';//note TTF 字库目录
	var $datapath	= '';//note 图片、声音、Flash 等数据目录
	//var $includepath= '';		//note 其它包含文件目录

	var $fontcolor;
	var $im;

         function __construct(array $options)
        { 
        
            $options=  array_merge(Hi::ini('seccodedata'),$options);
             
            if($options){
                $this->type = $options['type']?$options['type']:0;
                $this->width = $options['width']?$options['width']:150;
                $this->height = $options['height']?$options['height']:20;
                $this->background = $options['background']?$options['background']:1;
                $this->adulterate = $options['adulterate']?$options['adulterate']:1;
                $this->ttf = $options['ttf']?$options['ttf']:0;
                $this->angle = $options['angle']?$options['angle']:0;
                $this->color = $options['color']?$options['color']:1;
                $this->size = $options['size']?$options['size']:0;
                $this->shadow = $options['shadow']?$options['shadow']:1;
                $this->animator = $options['animator']?$options['animator']:0;              
            }
            $this->type == 2 && !extension_loaded('ming') && $this->type = 0;
	    $this->width = $this->width >= 100 && $this->width <= 200 ? $this->width : 150;
	    $this->height = $this->height >= 50 && $this->height <= 80 ? $this->height : 60;
            
            $this->fontpath	= HICORE_PATH.'./images/seccode/font/';
            $this->datapath	= HICORE_PATH.'./images/seccode/';

         }

        function generateCode(){

            $chnstring ='的一是在了不和有大这主中人上为们地个用工时要动国产以我到他会作来分生对于学下级就年阶义发成部民可出能方进同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批如应形想制心样干都向变关点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑';
            //global $_G;
            $seccode = util::random(6, 1);
            $seccodeunits = '';
            if($this->type == 1) {
                //$lang = lang('forum/seccode');
                //$len = strtoupper(Hi::ini('response_charset')) == 'GBK' ? 2 : 3;
                $len = 2;
                $code = array(substr($seccode, 0, 3), substr($seccode, 3, 3));
                $seccode = '';
                for($i = 0; $i < 2; $i++) {
                    $seccode .= substr($chnstring, $code[$i] * $len, $len);
                }
            } elseif($this->type == 3) {
                $s = sprintf('%04s', base_convert($seccode, 10, 20));
                $seccodeunits = 'CEFHKLMNOPQRSTUVWXYZ';
            } else {
                $s = sprintf('%04s', base_convert($seccode, 10, 24));
                $seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
            }
            if($seccodeunits) {
                $seccode = '';
                for($i = 0; $i < 4; $i++) {
                    $unit = ord($s{$i});
                    $seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
                }
            }

            $this->code=$seccode;
            //$this->session['seccode']=$seccode;
            //dsetcookie('seccode', md5(strtoupper($seccode)."\t".$_G['config']['security']['authkey']."\t".substr(TIMESTAMP, 0, 7)), SECCODE_LIFE);
            return $seccode;

            
        }


	function generateImage() {
		
		
		if($this->type < 2 && function_exists('imagecreate') && function_exists('imagecolorset') && function_exists('imagecopyresized') &&
			function_exists('imagecolorallocate') && function_exists('imagechar') && function_exists('imagecolorsforindex') &&
			function_exists('imageline') && function_exists('imagecreatefromstring') && (function_exists('imagegif') || function_exists('imagepng') || function_exists('imagejpeg'))) {
			$this->image();
		} elseif($this->type == 2 && extension_loaded('ming')) {
			$this->flash();
		} elseif($this->type == 3) {
			$this->audio();
		} else {
			$this->bitmap();
		}
	}

	function image() {
		$bgcontent = $this->background();

		if($this->animator == 1 && function_exists('imagegif')) {
			//include_once $this->includepath.'class_gifmerge.php';
			$trueframe = mt_rand(1, 9);

			for($i = 0; $i <= 9; $i++) {
				$this->im = imagecreatefromstring($bgcontent);
				$x[$i] = $y[$i] = 0;
				$this->adulterate && $this->adulterate();
				if($i == $trueframe) {
					$this->ttf && function_exists('imagettftext') || $this->type == 1 ? $this->ttffont() : $this->giffont();
					$d[$i] = mt_rand(250, 400);
				} else {
					$this->adulteratefont();
					$d[$i] = mt_rand(5, 15);
				}
				ob_start();
				imagegif($this->im);
				imagedestroy($this->im);
				$frame[$i] = ob_get_contents();
				ob_end_clean();
			}
			$anim = new GifMerge($frame, 255, 255, 255, 0, $d, $x, $y, 'C_MEMORY');
			header('Content-type: image/gif');
			echo $anim->getAnimation();
		} else {
			$this->im = imagecreatefromstring($bgcontent);
			$this->adulterate && $this->adulterate();
			$this->ttf && function_exists('imagettftext') || $this->type == 1 ? $this->ttffont() : $this->giffont();

			if(function_exists('imagepng')) {
				header('Content-type: image/png');
				imagepng($this->im);
			} else {
				header('Content-type: image/jpeg');
				imagejpeg($this->im, '', 100);
			}
			imagedestroy($this->im);
		}
	}

	function background() {
		$this->im = imagecreatetruecolor($this->width, $this->height);
		$backgrounds = $c = array();
		if($this->background && function_exists('imagecreatefromjpeg') && function_exists('imagecolorat') && function_exists('imagecopymerge') &&
			function_exists('imagesetpixel') && function_exists('imageSX') && function_exists('imageSY')) {
			if($handle = @opendir($this->datapath.'background/')) {
				while($bgfile = @readdir($handle)) {
					if(preg_match('/\.jpg$/i', $bgfile)) {
						$backgrounds[] = $this->datapath.'background/'.$bgfile;
					}
				}
				@closedir($handle);
			}
			if($backgrounds) {
				$imwm = imagecreatefromjpeg($backgrounds[array_rand($backgrounds)]);
				$colorindex = imagecolorat($imwm, 0, 0);
				$this->c = imagecolorsforindex($imwm, $colorindex);
				$colorindex = imagecolorat($imwm, 1, 0);
				imagesetpixel($imwm, 0, 0, $colorindex);
				$c[0] = $c['red'];$c[1] = $c['green'];$c[2] = $c['blue'];
				imagecopymerge($this->im, $imwm, 0, 0, mt_rand(0, 200 - $this->width), mt_rand(0, 80 - $this->height), imageSX($imwm), imageSY($imwm), 100);
				imagedestroy($imwm);
			}
		}
		if(!$this->background || !$backgrounds) {
			for($i = 0;$i < 3;$i++) {
				$start[$i] = mt_rand(200, 255);$end[$i] = mt_rand(100, 150);$step[$i] = ($end[$i] - $start[$i]) / $this->width;$c[$i] = $start[$i];
			}
			for($i = 0;$i < $this->width;$i++) {
				$color = imagecolorallocate($this->im, $c[0], $c[1], $c[2]);
				imageline($this->im, $i, 0, $i, $this->height, $color);
				$c[0] += $step[0];$c[1] += $step[1];$c[2] += $step[2];
			}
			$c[0] -= 20;$c[1] -= 20;$c[2] -= 20;
		}
		ob_start();
		if(function_exists('imagepng')) {
			imagepng($this->im);
		} else {
			imagejpeg($this->im, '', 100);
		}
		imagedestroy($this->im);
		$bgcontent = ob_get_contents();
		ob_end_clean();
		$this->fontcolor = $c;
		return $bgcontent;
	}

	function adulterate() {
		$linenums = $this->height / 10;
		for($i=0; $i <= $linenums; $i++) {
			$color = $this->color ? imagecolorallocate($this->im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)) : imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
			$x = mt_rand(0, $this->width);
			$y = mt_rand(0, $this->height);
			if(mt_rand(0, 1)) {
				imagearc($this->im, $x, $y, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, 360), mt_rand(0, 360), $color);
			} else {
				imageline($this->im, $x, $y, 0, mt_rand(0, mt_rand($this->height, $this->width)), $color);
			}
		}
	}

	function adulteratefont() {
		$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
		$x = $this->width / 4;
		$y = $this->height / 10;
		$text_color = imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
		for($i = 0; $i <= 3; $i++) {
			$adulteratecode = $seccodeunits[mt_rand(0, 23)];
			imagechar($this->im, 5, $x * $i + mt_rand(0, $x - 10), mt_rand($y, $this->height - 10 - $y), $adulteratecode, $text_color);
		}
	}

	function ttffont() {
		$seccode = $this->code;
		$seccoderoot = $this->type ? $this->fontpath.'ch/' : $this->fontpath.'en/';
		$dirs = opendir($seccoderoot);
		$seccodettf = array();
		while($entry = readdir($dirs)) {
			if($entry != '.' && $entry != '..' && in_array(strtolower(util::fileext($entry)), array('ttf', 'ttc'))) {
				$seccodettf[] = $entry;
			}
		}
		if(empty($seccodettf)) {
			$this->giffont();
			return;
		}
		$seccodelength = 4;
		if($this->type && !empty($seccodettf)) {
			/*
			if(strtoupper(Hi::ini('response_charset')) != 'UTF-8') {
				//include $this->includepath.'class_chinese.php';
				$cvt = new Chinese(Hi::ini('response_charset'), 'utf8');
				//$cvt = new Chinese('GB2312', 'utf8');
				$seccode = $cvt->Convert($seccode);
				//echo '--'.$seccode;
				//exit;
			}
			*/
			$cvt = new Chinese('gbk', 'utf8');
			$seccode = $cvt->Convert($seccode);
			
			$seccode = array(substr($seccode, 0, 3), substr($seccode, 3, 3));
			$seccodelength = 2;
		}
		$widthtotal = 0;
		for($i = 0; $i < $seccodelength; $i++) {
			$font[$i]['font'] = $seccoderoot.$seccodettf[array_rand($seccodettf)];
			$font[$i]['angle'] = $this->angle ? mt_rand(-30, 30) : 0;
			$font[$i]['size'] = $this->type ? $this->width / 7 : $this->width / 6;
			$this->size && $font[$i]['size'] = mt_rand($font[$i]['size'] - $this->width / 40, $font[$i]['size'] + $this->width / 20);
			$box = imagettfbbox($font[$i]['size'], 0, $font[$i]['font'], $seccode[$i]);
			$font[$i]['zheight'] = max($box[1], $box[3]) - min($box[5], $box[7]);
			$box = imagettfbbox($font[$i]['size'], $font[$i]['angle'], $font[$i]['font'], $seccode[$i]);
			$font[$i]['height'] = max($box[1], $box[3]) - min($box[5], $box[7]);
			$font[$i]['hd'] = $font[$i]['height'] - $font[$i]['zheight'];
			$font[$i]['width'] = (max($box[2], $box[4]) - min($box[0], $box[6])) + mt_rand(0, $this->width / 8);
			$font[$i]['width'] = $font[$i]['width'] > $this->width / $seccodelength ? $this->width / $seccodelength : $font[$i]['width'];
			$widthtotal += $font[$i]['width'];
		}
		$x = mt_rand($font[0]['angle'] > 0 ? cos(deg2rad(90 - $font[0]['angle'])) * $font[0]['zheight'] : 1, $this->width - $widthtotal);
		!$this->color && $text_color = imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
		for($i = 0; $i < $seccodelength; $i++) {
			if($this->color) {
				$this->fontcolor = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
				$this->shadow && $text_shadowcolor = imagecolorallocate($this->im, 0, 0, 0);
				$text_color = imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
			} elseif($this->shadow) {
				$text_shadowcolor = imagecolorallocate($this->im, 0, 0, 0);
			}
			$y = $font[0]['angle'] > 0 ? mt_rand($font[$i]['height'], $this->height) : mt_rand($font[$i]['height'] - $font[$i]['hd'], $this->height - $font[$i]['hd']);
			$this->shadow && imagettftext($this->im, $font[$i]['size'], $font[$i]['angle'], $x + 1, $y + 1, $text_shadowcolor, $font[$i]['font'], $seccode[$i]);
			imagettftext($this->im, $font[$i]['size'], $font[$i]['angle'], $x, $y, $text_color, $font[$i]['font'], $seccode[$i]);
			$x += $font[$i]['width'];
		}
	}

	function giffont() {
		$seccode = $this->code;
		$seccodedir = array();
		if(function_exists('imagecreatefromgif')) {
			$seccoderoot = $this->datapath.'gif/';
			$dirs = opendir($seccoderoot);
			while($dir = readdir($dirs)) {
				if($dir != '.' && $dir != '..' && file_exists($seccoderoot.$dir.'/9.gif')) {
					$seccodedir[] = $dir;
				}
			}
		}
		$widthtotal = 0;
		for($i = 0; $i <= 3; $i++) {
			$this->imcodefile = $seccodedir ? $seccoderoot.$seccodedir[array_rand($seccodedir)].'/'.strtolower($seccode[$i]).'.gif' : '';
			if(!empty($this->imcodefile) && file_exists($this->imcodefile)) {
				$font[$i]['file'] = $this->imcodefile;
				$font[$i]['data'] = getimagesize($this->imcodefile);
				$font[$i]['width'] = $font[$i]['data'][0] + mt_rand(0, 6) - 4;
				$font[$i]['height'] = $font[$i]['data'][1] + mt_rand(0, 6) - 4;
				$font[$i]['width'] += mt_rand(0, $this->width / 5 - $font[$i]['width']);
				$widthtotal += $font[$i]['width'];
			} else {
				$font[$i]['file'] = '';
				$font[$i]['width'] = 8 + mt_rand(0, $this->width / 5 - 5);
				$widthtotal += $font[$i]['width'];
			}
		}
		$x = mt_rand(1, $this->width - $widthtotal);
		for($i = 0; $i <= 3; $i++) {
			$this->color && $this->fontcolor = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			if($font[$i]['file']) {
				$this->imcode = imagecreatefromgif($font[$i]['file']);
				if($this->size) {
					$font[$i]['width'] = mt_rand($font[$i]['width'] - $this->width / 20, $font[$i]['width'] + $this->width / 20);
					$font[$i]['height'] = mt_rand($font[$i]['height'] - $this->width / 20, $font[$i]['height'] + $this->width / 20);
				}
				$y = mt_rand(0, $this->height - $font[$i]['height']);
				if($this->shadow) {
					$this->imcodeshadow = $this->imcode;
					imagecolorset($this->imcodeshadow, 0, 0, 0, 0);
					imagecopyresized($this->im, $this->imcodeshadow, $x + 1, $y + 1, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
				}
				imagecolorset($this->imcode, 0 , $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
				imagecopyresized($this->im, $this->imcode, $x, $y, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
			} else {
				$y = mt_rand(0, $this->height - 20);
				if($this->shadow) {
					$text_shadowcolor = imagecolorallocate($this->im, 0, 0, 0);
					imagechar($this->im, 5, $x + 1, $y + 1, $seccode[$i], $text_shadowcolor);
				}
				$text_color = imagecolorallocate($this->im, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
				imagechar($this->im, 5, $x, $y, $seccode[$i], $text_color);
			}
			$x += $font[$i]['width'];
		}
	}

	function flash() {
		$spacing = 5;
		$codewidth = ($this->width - $spacing * 5) / 4;
		$strforswdaction = '';
		for($i = 0; $i <= 3; $i++) {
			$strforswdaction .= $this->swfcode($codewidth, $spacing, $this->code[$i], $i+1);
		}

		ming_setScale(20.00000000);
		ming_useswfversion(6);
		$movie = new SWFMovie();
		$movie->setDimension($this->width, $this->height);
		$movie->setBackground(255, 255, 255);
		$movie->setRate(31);

		$fontcolor = '0x'.(sprintf('%02s', dechex (mt_rand(0, 255)))).(sprintf('%02s', dechex (mt_rand(0, 128)))).(sprintf('%02s', dechex (mt_rand(0, 255))));
		$strAction = "
		_root.createEmptyMovieClip ( 'triangle', 1 );
		with ( _root.triangle ) {
		lineStyle( 3, $fontcolor, 100 );
		$strforswdaction
		}
		";
		$movie->add(new SWFAction( str_replace("\r", "", $strAction) ));
		header('Content-type: application/x-shockwave-flash');
		$movie->output();
	}

	function swfcode($width, $d, $code, $order) {
		$str = '';
		$height = $this->height - $d * 2;
		$x_0 = ($order * ($width + $d) - $width);
		$x_1 = $x_0 + $width / 2;
		$x_2 = $x_0 + $width;
		$y_0 = $d;
		$y_2 = $y_0 + $height;
		$y_1 = $y_2 / 2;
		$y_0_5 = $y_2 / 4;
		$y_1_5 = $y_1 + $y_0_5;
		switch($code) {
			case 'B':$str .= 'moveTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_1.', '.$y_2.');lineTo('.$x_2.', '.$y_1_5.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
			case 'C':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');';break;
			case 'E':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
			case 'F':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
			case 'G':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
			case 'H':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
			case 'J':$str .= 'moveTo('.$x_1.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_1_5.');';break;
			case 'K':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');';break;
			case 'M':$str .= 'moveTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');';break;
			case 'P':$str .= 'moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');';break;
			case 'Q':$str .= 'moveTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_1.', '.$y_1.');';break;
			case 'R':$str .= 'moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');';break;
			case 'T':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');moveTo('.$x_1.', '.$y_0.');lineTo('.$x_1.', '.$y_2.');';break;
			case 'V':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');';break;
			case 'W':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');';break;
			case 'X':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');';break;
			case 'Y':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_1.', '.$y_2.');';break;
			case '2':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');';break;
			case '3':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
			case '4':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
			case '6':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');';break;
			case '7':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');';break;
			case '8':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
			case '9':$str .= 'moveTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');';break;
		}
		return $str;
	}

	function audio() {
		header('Content-type: audio/mpeg');
		for($i = 0;$i <= 3; $i++) {
			readfile($this->datapath.'sound/'.strtolower($this->code[$i]).'.mp3');
		}
	}

	function bitmap() {
		$numbers = array
			(
			'B' => array('00','fc','66','66','66','7c','66','66','fc','00'),
			'C' => array('00','38','64','c0','c0','c0','c4','64','3c','00'),
			'E' => array('00','fe','62','62','68','78','6a','62','fe','00'),
			'F' => array('00','f8','60','60','68','78','6a','62','fe','00'),
			'G' => array('00','78','cc','cc','de','c0','c4','c4','7c','00'),
			'H' => array('00','e7','66','66','66','7e','66','66','e7','00'),
			'J' => array('00','f8','cc','cc','cc','0c','0c','0c','7f','00'),
			'K' => array('00','f3','66','66','7c','78','6c','66','f7','00'),
			'M' => array('00','f7','63','6b','6b','77','77','77','e3','00'),
			'P' => array('00','f8','60','60','7c','66','66','66','fc','00'),
			'Q' => array('00','78','cc','cc','cc','cc','cc','cc','78','00'),
			'R' => array('00','f3','66','6c','7c','66','66','66','fc','00'),
			'T' => array('00','78','30','30','30','30','b4','b4','fc','00'),
			'V' => array('00','1c','1c','36','36','36','63','63','f7','00'),
			'W' => array('00','36','36','36','77','7f','6b','63','f7','00'),
			'X' => array('00','f7','66','3c','18','18','3c','66','ef','00'),
			'Y' => array('00','7e','18','18','18','3c','24','66','ef','00'),
			'2' => array('fc','c0','60','30','18','0c','cc','cc','78','00'),
			'3' => array('78','8c','0c','0c','38','0c','0c','8c','78','00'),
			'4' => array('00','3e','0c','fe','4c','6c','2c','3c','1c','1c'),
			'6' => array('78','cc','cc','cc','ec','d8','c0','60','3c','00'),
			'7' => array('30','30','38','18','18','18','1c','8c','fc','00'),
			'8' => array('78','cc','cc','cc','78','cc','cc','cc','78','00'),
			'9' => array('f0','18','0c','6c','dc','cc','cc','cc','78','00')
			);

		foreach($numbers as $i => $number) {
			for($j = 0; $j < 6; $j++) {
				$a1 = substr('012', mt_rand(0, 2), 1).substr('012345', mt_rand(0, 5), 1);
				$a2 = substr('012345', mt_rand(0, 5), 1).substr('0123', mt_rand(0, 3), 1);
				mt_rand(0, 1) == 1 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a1);
				mt_rand(0, 1) == 0 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a2);
			}
		}

		$bitmap = array();
		for($i = 0; $i < 20; $i++) {
			for($j = 0; $j <= 3; $j++) {
				$bytes = $numbers[$this->code[$j]][$i];
				$a = mt_rand(0, 14);
				array_push($bitmap, $bytes);
			}
		}

		for($i = 0; $i < 8; $i++) {
			$a = substr('012345', mt_rand(0, 2), 1) . substr('012345', mt_rand(0, 5), 1);
			array_unshift($bitmap, $a);
			array_push($bitmap, $a);
		}

		$image = pack('H*', '424d9e000000000000003e000000280000002000000018000000010001000000'.
				'0000600000000000000000000000000000000000000000000000FFFFFF00'.implode('', $bitmap));

		header('Content-Type: image/bmp');
		echo $image;
	}

}

?>