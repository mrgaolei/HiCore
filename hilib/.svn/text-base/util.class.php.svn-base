<?php

!defined('HICORE_PATH') && exit('Access Denied');

class util {

	function util() {
		die("Class util can not instantiated!");
	}

	static function date($time, $type = 3, $friendly=0) {
		if (!$time)
		return '';
		$now = time();
		$format[] = $type & 2 ? 'Y-n-j' : '';
		$format[] = $type & 1 ? 'H:i' : '';
		$timestring = date(implode(' ', $format), $time);
		if ($friendly) {
			$dtime = $now - $time;
			if ($dtime < 1)
			return $timestring;
			$dday = intval(date('Ymd', $now)) - intval(date('Ymd', $time));
			$dyear = intval(date('Y', $now)) - intval(date('Y', $time));
			if ($dtime < 60) {
				$timestring = $dtime . '秒前';
			} elseif ($dtime < 3600) {
				$timestring = intval($dtime / 60) . '分钟前';
			} elseif ($dtime < 86400) {
				$timestring = intval($dtime / 3600) . '小时前';
			} elseif ($dtime < (86400 * 2)) {
				$timestring = '昨天';
			} elseif ($dtime < (86400 * 3)) {
				$timestring = '前天';
			} elseif ($dtime < (86400 * 7)) {
				$timestring = intval($dtime / 86400) . '天前';
			}
		}
		return $timestring;
	}

	//处理上传图片连接
	function pic_get($filepath, $thumb, $remote, $return_thumb=1) {

		if (empty($filepath)) {
			$url = 'images/nopic.gif';
		} else {
			$url = $filepath;
			if ($return_thumb && $thumb)
			$url .= '.thumb.jpg';
		}

		return $url;
	}

	static function makeurl($controller = 'index', $action = 'default', $args = array(), $entery = null, $urlmode = 0) {
		$defurlmode = Hi::ini('url_mode');
		$urlmode = $urlmode ? $urlmode : $defurlmode;
		$url = "";
		switch ($urlmode) {
			case 1:
				//                    if ($entery) $entery = "/$entery";
				$url = "$entery/$controller/$action/";
				foreach ($args as $k => $v) {
					$url .= "$k/$v/";
				}
				break;
			case 2:
				if (is_null($entery)) {
					$entery = "";
				} elseif ($entery == "/" || $entery == "") {
					$entery = "/";
				} else {
					$entery = "/$entery/";
				}
				$url = "{$entery}index.php?c=$controller&a=$action";
				foreach ($args as $k => $v) {
					$url .= "&$k=$v";
				}
				break;
			case 3:
				if ($controller == 'index' && $action == 'default') {
					$url = "/";
				} elseif ($action == 'default') {
					$url = "/$controller/";
				} else {
					$url = "/$controller/$action/";
				}
				if (count($args)) {
					foreach ($args as $k => $v) {
						$v = str_replace('-', '~', $v);
						$url .= "$k-$v-";
					}
					$url = preg_replace("/\-$/i", "", $url);
					$url .= ".html";
				}
				break;
			default:
				die ('URL_MODE does not supported!');
				break;
		}
		$urlmode = $defurlmode;
		return $url;
	}

	/**
	 * random
	 * @param int $length
	 * @return string $hash
	 */
	static function random($length=6, $type=0) {
		$hash = '';
		$chararr = array(
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',
            '0123456789',
            'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
            );
            $chars = $chararr[$type];
            $max = strlen($chars) - 1;
            PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
            for ($i = 0; $i < $length; $i++) {
            	$hash .= $chars[mt_rand(0, $max)];
            }
            return $hash;
	}

	/**
	 * image_compress
	 * @param string $url,$prefix;int  $width,$height
	 * @return array $result
	 */
	static function image_compress($url, $prefix='s_', $width=80, $height=60, $suffix='') {
		global $lang;
		$result = array('result' => false, 'tempurl' => '', 'msg' => 'something Wrong');
		if (!file_exists($url)) {
			$result['msg'] = $url . 'img is not exist';
			return $result;
		}
		$urlinfo = pathinfo($url);
		$ext = strtolower($urlinfo['extension']);
		$tempurl = $urlinfo['dirname'] . '/' . $prefix . substr($urlinfo['basename'], 0, -1 - strlen($ext)) . $suffix . '.' . $ext;
		if (!util::isimage($ext)) {
			$result['msg'] = 'img must be gif|jpg|jpeg|png';
			return $result;
		}
		$ext = ($ext == 'jpg') ? 'jpeg' : $ext;
		$createfunc = 'imagecreatefrom' . $ext;
		$imagefunc = 'image' . $ext;
		if (function_exists($createfunc)) {
			list($actualWidth, $actualHeight) = getimagesize($url);
			if ($actualWidth < $width && $actualHeight < $height) {
				copy($url, $tempurl);
				$result['tempurl'] = $tempurl;
				$result['result'] = true;
				return $result;
			}
			if ($actualWidth < $actualHeight) {
				$width = round(($height / $actualHeight) * $actualWidth);
			} else {
				$height = round(($width / $actualWidth) * $actualHeight);
			}
			$tempimg = imagecreatetruecolor($width, $height);
			$img = $createfunc($url);
			imagecopyresampled($tempimg, $img, 0, 0, 0, 0, $width, $height, $actualWidth, $actualHeight);
			$result['result'] = ($ext == 'png') ? $imagefunc($tempimg, $tempurl) : $imagefunc($tempimg, $tempurl, 80);

			imagedestroy($tempimg);
			imagedestroy($img);
			if (file_exists($tempurl)) {
				$result['tempurl'] = $tempurl;
			} else {
				$result['tempurl'] = $url;
			}
		} else {
			copy($url, $tempurl);
			if (file_exists($tempurl)) {
				$result['result'] = true;
				$result['tempurl'] = $tempurl;
			} else {
				$result['tempurl'] = $url;
			}
		}
		return $result;
	}

	/**
	 * isimage
	 * @param string $extname
	 * @return true or false
	 */
	static function isimage($extname) {
		return in_array($extname, array('jpg', 'jpeg', 'png', 'gif'));
	}

	/**
	 * getfirstimg
	 * @param string $content
	 * @return string $tempurl
	 */
	static function getfirstimg($string) {
		preg_match("/<img.+?src=[\\\\]?\"(.+?)[\\\\]?\"/i", $string, $imgs);
		if (isset($imgs[1])) {
			return $imgs[1];
		} else {
			return "";
		}
	}

	static function getimagesnum($string) {
		preg_match_all("/<img.+?src=[\\\\]?\"(.+?)[\\\\]?\"/i", $string, $imgs);
		return count($imgs[0]);
	}

	/**
	 * formatfilesize
	 *
	 * @param int $size
	 * @return string $_format
	 */
	static function formatfilesize($filename) {
		$size = filesize($filename);
		if ($size < 1024) {
			$_format = $size . "B";
			return $_format;
		} elseif ($size < 1024 * 1024) {
			$_format = round($size / 1024, 2) . "KB";
			return $_format;
		} elseif ($size < 1024 * 1024 * 1024) {
			$_format = round($size / (1024 * 1024), 2) . "MB";
			return $_format;
		}
	}

	static function formatfilesize2($size) {
		//$size=filesize($filename);
		if ($size < 1024) {
			$_format = $size . "B";
			return $_format;
		} elseif ($size < 1024 * 1024) {
			$_format = round($size / 1024, 2) . "KB";
			return $_format;
		} elseif ($size < 1024 * 1024 * 1024) {
			$_format = round($size / (1024 * 1024), 2) . "MB";
			return $_format;
		}
	}

	/**
	 * getip
	 *
	 * @return string
	 */
	static function getip() {
		if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} else if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$ip = getenv('REMOTE_ADDR');
		} else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		preg_match("/[\d\.]{7,15}/", $ip, $temp);
		$ip = $temp[0] ? $temp[0] : 'unknown';
		unset($temp);
		return $ip;
	}

	static function makecode($code) {
		$codelen = strlen($code);
		$im = imagecreate(50, 20);
		$font_type = HDWIKI_ROOT . "/style/default/ant2.ttf";
		$bgcolor = ImageColorAllocate($im, 235, 245, 255); //近白色
		$iborder = ImageColorAllocate($im, 70, 80, 90); //近黑色

		$fontColor = ImageColorAllocate($im, 164, 164, 164);
		$fontColor1 = ImageColorAllocate($im, 20, 80, 255); //近蓝色
		$fontColor2 = ImageColorAllocate($im, 50, 50, 50); //近黑色
		$fontColor3 = ImageColorAllocate($im, 255, 80, 20); //近红色
		$fontColor4 = ImageColorAllocate($im, 20, 200, 20); //近绿色

		$lineColor = ImageColorAllocate($im, 110, 220, 220); //淡蓝色

		for ($j = 3; $j <= 16; $j = $j + 4)
		imageline($im, 2, $j, 48, $j, $lineColor);
		for ($j = 2; $j < 52; $j = $j + (mt_rand(3, 6)))
		imageline($im, $j, 2, $j - 6, 18, $lineColor);
		imagerectangle($im, 0, 0, 49, 19, $iborder);
		$strposs = array();
		for ($i = 0; $i < $codelen; $i++) {
			if (function_exists("imagettftext")) {
				$strposs[$i][0] = $i * 10 + 6;
				$strposs[$i][1] = mt_rand(15, 18);
				imagettftext($im, 11, 5, $strposs[$i][0] + 1, $strposs[$i][1] + 1, $fontColor, $font_type, $code[$i]);
			} else {
				imagestring($im, 5, $i * 10 + 6, mt_rand(2, 4), $code[$i], $fontColor2);
			}
		}
		for ($i = 0; $i < $codelen; $i++) {
			if (function_exists("imagettftext")) {
				$fontC = ${'fontColor' . mt_rand(1, 4)};
				imagettftext($im, 11, 5, $strposs[$i][0], $strposs[$i][1], $fontC, $font_type, $code[$i]);
			}
		}
		header("Pragma:no-cache\r\n");
		header("Cache-Control:no-cache\r\n");
		header("Expires:0\r\n");
		if (function_exists("imagejpeg")) {
			header("content-type:image/jpeg\r\n");
			imagejpeg($im);
		} else {
			header("content-type:image/png\r\n");
			imagepng($im);
		}
		ImageDestroy($im);
	}

	static function hfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
		$return = '';
		$matches = parse_url($url);
		$host = $matches['host'];
		@$path = $matches['path'] ? $matches['path'] . '?' . $matches['query'] . '#' . $matches['fragment'] : '/';
		$port = !empty($matches['port']) ? $matches['port'] : 80;

		if ($post) {
			$out = "POST $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			//$out .= "Referer: $site_url\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= 'Content-Length: ' . strlen($post) . "\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cache-Control: no-cache\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
			$out .= $post;
		} else {
			$out = "GET $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			//$out .= "Referer: $site_url\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
		}
		$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
		if (!$fp) {
			return '';
		} else {
			stream_set_blocking($fp, $block);
			stream_set_timeout($fp, $timeout);
			@fwrite($fp, $out);
			$status = stream_get_meta_data($fp);
			if (!$status['timed_out']) {
				$firstline = true;
				while (!feof($fp)) {
					$header = @fgets($fp);
					if ($firstline && (false === strstr($header, '200'))) {
						return '';
					}
					$firstline = $false;
					if ($header && ($header == "\r\n" || $header == "\n")) {
						break;
					}
				}
				$stop = false;
				while (!feof($fp) && !$stop) {
					$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
					$return .= $data;
					if ($limit) {
						$limit -= strlen($data);
						$stop = $limit <= 0;
					}
				}
			}
			@fclose($fp);
			return $return;
		}
	}

	static function is_mem_available($mem) {
		$limit = trim(ini_get('memory_limit'));
		if (empty($limit))
		return true;
		$unit = strtolower(substr($limit, -1));
		switch ($unit) {
			case 'g':
				$limit = substr($limit, 0, -1);
				$limit *= 1024 * 1024 * 1024;
				break;
			case 'm':
				$limit = substr($limit, 0, -1);
				$limit *= 1024 * 1024;
				break;
			case 'k':
				$limit = substr($limit, 0, -1);
				$limit *= 1024;
				break;
		}
		if (function_exists('memory_get_usage')) {
			$used = memory_get_usage();
		}
		if ($used + $mem > $limit) {
			return false;
		}
		return true;
	}

	static function strcode($string, $action='ENCODE') {
		$key = substr(md5($_SERVER["HTTP_USER_AGENT"] . PP_KEY), 8, 18);
		$string = $action == 'ENCODE' ? $string : base64_decode($string);
		$len = strlen($key);
		$code = '';
		for ($i = 0; $i < strlen($string); $i++) {
			$k = $i % $len;
			$code .= $string[$i] ^ $key[$k];
		}
		$code = $action == 'DECODE' ? $code : base64_encode($code);
		return $code;
	}

	static function in_rowfield($value, $fieldname, $rowarray) {
		foreach ($rowarray as $row) {
			foreach ($row as $rowvalue) {
				if ($rowvalue[$fieldname] == $value)
				return true;
			}
		}
		return false;
	}

	static function fileext($filename) {
		return trim(substr(strrchr($filename, '.'), 1, 10));
	}

	/**
	 * 获取根目录url
	 */
	static function getBaseURL() {
		return str_replace(str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']), 'http://' . $_SERVER['HTTP_HOST'], str_replace("\\", "/", INDEX_ROOT));
	}

	/**
	 * 常用日期格式 (Y-m-d H:i:s)(Y-m-d) 时间转换为 Unix 时间戳
	 *
	 * @param string $str
	 */
	static function date2timestamp($str='') {
		if (!$str)
		return time();
		if (preg_match("/(\d{4})\-(\d{1,2})\-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})/", $str, $arr)) {
			$timestr = mktime($arr[4], $arr[5], $arr[6], $arr[2], $arr[3], $arr[1]);
		} elseif (preg_match("/(\d{4})\-(\d{1,2})\-(\d{1,2})/", $str, $arr)) {
			$timestr = mktime(0, 0, 0, $arr[2], $arr[3], $arr[1]);
		} else {
			$timestr = false;
		}
		return $timestr;
	}

	static function iconv($pramas, $incharset = 'gbk', $outcharset = 'utf-8') {
		if (empty($pramas))
		return;
		if (is_array($pramas)) {
			foreach ($pramas as $k => $v) {
				$pramas[$k] = self::iconv($v, $incharset, $outcharset);
			}
		} else {
			$pramas = iconv($incharset, $outcharset, $pramas);
		}
		return $pramas;
	}

	//获取字符串
	static function getstr($string, $length, $in_slashes=0, $out_slashes=0, $censor=0, $bbcode=0, $html=0, $type=1) {
		$string = trim($string);

		if ($in_slashes) {
			//传入的字符有slashes
			$string = self::sstripslashes($string);
		}
		if ($html < 0) {
			//去掉html标签
			//$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\]|'&lt;'|'br'|\\|'&gt;'|'&nbsp; ')/is", '', $string);
			$string = strip_tags($string);
			$string = self::shtmlspecialchars($string);
		} elseif ($html == 0) {
			//转换html标签
			$string = self::shtmlspecialchars($string);
		}
		if ($length && strlen($string) > $length) {
			//截断字符
			$wordscut = '';
			for ($i = 0; $i < $length - 1; $i++) {
				if (ord($string[$i]) > 127) {
					$wordscut .= $string[$i] . $string[$i + 1];
					$i++;
				} else {
					$wordscut .= $string[$i];
				}
			}
			$string = mb_substr($string,0,$length,'UTF-8');
		}
		if ($bbcode) {
			$string = self::bbcode($string, $bbcode);
		}
		if ($out_slashes) {
			$string = self::saddslashes($string, $type);
		}
		return trim($string);
	}

	//处理模块
	function bbcode($message, $parseurl=0) {
		$search_exp = '';

		if (empty($search_exp)) {
			$search_exp = array(
                "/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is",
                "/\[url\]\s*(https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|ed2k:\/\/){1}([^\[\"']+?)\s*\[\/url\]/i",
                "/\[em:(.+?):\]/i",
			);
			$replace_exp = array(
                "<div class=\"quote\"><span class=\"q\">\\1</span></div>",
                "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",
                "<img src=\"images/face/\\1.gif\" class=\"face\">"
                );
                $search_str = array('[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]');
                $replace_str = array('<b>', '</b>', '<i>', '</i>', '<u>', '</u>');
		}

		if ($parseurl == 2) {//深度解析
			$search_exp[] = "/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies";
			$replace_exp[] = 'self::bb_img(\'\\1\')';
			$message = self::parseurl($message);
		}
		$message = str_replace($search_str, $replace_str, preg_replace($search_exp, $replace_exp, $message, 20));
		return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
	}

	function bb_img($url) {
		$url = addslashes($url);
		return "<img src=\"$url\">";
	}

	//自动解析url
	function parseurl($message) {
		return preg_replace("/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/i", "[url]\\1\\3[/url]", ' ' . $message);
	}

	//SQL ADDSLASHES
	function saddslashes($string, $type) {
		if (is_array($string)) {
			foreach ($string as $key => $val) {
				$string[$key] = self::saddslashes($val);
			}
		} else {
			if ($type)
			$string = addslashes($string);
		}
		return $string;
	}

	//去掉slassh
	function sstripslashes($string) {
		if (is_array($string)) {
			foreach ($string as $key => $val) {
				$string[$key] = sstripslashes($val);
			}
		} else {
			$string = stripslashes($string);
		}
		return $string;
	}

	//取消HTML代码
	function shtmlspecialchars($string) {
		if (is_array($string)) {
			foreach ($string as $key => $val) {
				$string[$key] = shtmlspecialchars($val);
			}
		}
		return $string;
	}

	//编码转换
	function siconv($str, $out_charset, $in_charset='') {

		$in_charset = empty($in_charset) ? strtoupper('gbk') : strtoupper($in_charset);
		$out_charset = strtoupper($out_charset);
		if ($in_charset != $out_charset) {
			if (function_exists('iconv') && (@$outstr = iconv("$in_charset//IGNORE", "$out_charset//IGNORE", $str))) {
				return $outstr;
			} elseif (function_exists('mb_convert_encoding') && (@$outstr = mb_convert_encoding($str, $out_charset, $in_charset))) {
				return $outstr;
			}
		}
		return $str; //转换失败
	}

	//html转化为bbcode
	function html2bbcode($message) {

		if (empty($html_s_exp)) {
			$html_s_exp = array(
                "/\<div class=\"quote\"\>\<span class=\"q\"\>(.*?)\<\/span\>\<\/div\>/is",
                "/\<a href=\"(.+?)\".*?\<\/a\>/is",
                "/(\r\n|\n|\r)/",
                "/<br.*>/siU",
                "/[ \t]*\<img src=\"image\/face\/(.+?).gif\".*?\>[ \t]*/is",
                "/\s*\<img src=\"(.+?)\".*?\>\s*/is"
                );
                $html_r_exp = array(
                "[quote]\\1[/quote]",
                "\\1",
                '',
                "\n",
                "[em:\\1:]",
                "\n[img]\\1[/img]\n"
                );
                $html_s_str = array('<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;', '&lt;', '&gt;', '&amp;');
                $html_s_str = "";
                $html_r_str = array('[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]', "\t", '   ', '  ', '<', '>', '&');
		}

		@$message = str_replace($html_s_str, $html_r_str,
		preg_replace($html_s_exp, $html_r_exp, $message));

		$message = self::shtmlspecialchars($message);

		return trim($message);
	}

	/*
	 * 格式转换
	 */

	static function transform($array, $num=0) {

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = self::transform($value);
			} else {
				if ($num == 0) {
					$array[$key] = iconv('gbk', 'utf-8', $value);
				} else {
					$array[$key] = iconv('utf-8', 'gbk', $value);
				}
			}
		}

		return $array;
	}

	/*
	 * 处理相差时间
	 */

	static function spacetime($days, $second = 0, $nowtime='') {
		if (empty($nowtime))
		$nowtime = time();
		else
		$nowtime=$nowtime;
		$date_time_array = getdate($nowtime);
		$hours = $date_time_array["hours"];
		$minutes = $date_time_array["minutes"];
		$seconds = $date_time_array["seconds"];
		$month = $date_time_array["mon"];
		$day = $date_time_array["mday"];
		$year = $date_time_array["year"];
		$timestamp = mktime($hours, $minutes, $seconds + $second, $month, $day - $days, $year);
		return $timestamp;
	}

	//格式化大小函数
	static function formatsize($size) {
		$prec = 3;
		$size = round(abs($size));
		$units = array(0 => " B ", 1 => " KB", 2 => " MB", 3 => " GB", 4 => " TB");
		if ($size == 0)
		return str_repeat(" ", $prec) . "0$units[0]";
		$unit = min(4, floor(log($size) / log(2) / 10));
		$size = $size * pow(2, -10 * $unit);
		$digi = $prec - 1 - floor(log($size) / log(10));
		$size = round($size * pow(10, $digi)) * pow(10, -$digi);
		return $size . $units[$unit];
	}

	//检查屏蔽通知
	static function cknote_uid($note, $filter) {

		if ($filter) {
			$key = $note['type'] . '|0';
			if (in_array($key, $filter)) {
				return false;
			} else {
				$key = $note['type'] . '|' . $note['authorid'];
				if (in_array($key, $filter)) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 取得一组 id 数组或逗号分隔的字符串
	 * @param array $data 数组源
	 * @param string $idColumn 要取得的字段
	 * @param bool $returnStr 是否返回字符串（默认为返回数组）
	 */
	static function getIds($data = array(), $idColumn = 'id', $returnStr = false) {
		$idres = array();
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$idres[] = $v[$idColumn];
			}
		}
		if ($returnStr)
		return implode(',', $idres);
		return $idres;
	}

	/**
	 * 按规则生成虚拟卡SN
	 *
	 * @param int $sn_rule
	 */
	static function createSNunit($sn_rule = 0) {
		$sn = mt_rand(100000, 999999);
		return $sn;
	}

	/**
	 * 过滤一维数组中为空为零的元素
	 *
	 * @param array $arr 一维数组
	 */
	static function filterNull($arr) {
		if (is_array($arr)) {
			foreach ($arr as $k => $v) {
				if (!$v)
				unset($arr[$k]);
			}
		}
		return $arr;
	}

	/**
	 * 构造SQL的in语句
	 *
	 * @param string $str 以逗号做分隔的id字串
	 * @param string $key 查询的列名
	 */
	static function mksqlin($str, $key) {
		$str = trim($str, ', ');
		if (substr_count($str, ',')) {
			$in_sql = "`$key` in ($str)";
		} else {
			$in_sql = "`$key` = '$str'";
		}
		return $in_sql;
	}

	//获取日志图片
	static function getmessagepic($message) {
		$pic = '';
		$message = stripslashes($message);
		$message = preg_replace("/\<img src=\".*?image\/face\/(.+?).gif\".*?\>\s*/is", '', $message); //移除表情符
		preg_match("/src\=[\"\']*([^\>\s]{25,105})\.(jpg|gif|png)/i", $message, $mathes);
		if (!empty($mathes[1]) || !empty($mathes[2])) {
			$pic = "{$mathes[1]}.{$mathes[2]}";
		}
		return addslashes($pic);
	}


	public static function userface($uid, $size = 2,$width = 0) {
		if (!in_array($size, array(1,2,3))) {
			$size = 2;
		}
		$hash = substr(md5($uid), 0, 2);
		if($width == 1){
			$html = "<img width=150 height=150 src=\"http://".FACE_DOMAIN."/face/$hash/$uid/$size.jpg\" onerror=\"this.src='".Hi::ini('SITE_URL/static')."/defaultface/face$size.jpg'\" />";
		}else{
			$html = "<img src=\"http://".FACE_DOMAIN."/face/$hash/$uid/$size.jpg\" onerror=\"this.src='".Hi::ini('SITE_URL/static')."/defaultface/face$size.jpg'\" />";
		}
		return $html;
			
	}

	/**
	 * 匹配支付信息
	 *
	 * @access  public
	 * @param   string       $cfg
	 * @return  void
	 */
	public function unserialize_config($cfg) {
		if (is_string($cfg) && ($arr = unserialize($cfg)) !== false) {
			$config = array();

			foreach ($arr AS $key => $val) {
				$config[$val['name']] = $val['value'];
			}

			return $config;
		} else {
			return false;
		}
	}

	/**
	 * 加密
	 * Enter description here ...
	 * @param unknown_type $Key 加密串
	 * @param unknown_type $value   内容
	 */
	public function encrypt($key,$value){
		$td = mcrypt_module_open("tripledes", "", "ecb", "");
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$encrypted_data = mcrypt_generic($td, $value);
		$data = bin2hex($encrypted_data);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $data;
	}

	/**
	 * 解密
	 * Enter description here ...
	 * @param unknown_type $key 解密串
	 * @param unknown_type $value 内容
	 */
	public function decrypt($key,$value){
		$value = pack("H*",$value);
		$td = mcrypt_module_open("tripledes", "", "ecb", "");
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$data = trim(mdecrypt_generic($td, $value));
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $data;
	}

	function splithtml($html,$preg='/(<h2\s+class=\"?doc_header1\"?>.+?<\/h2>)/i'){
		$arrhtml=preg_split($preg,$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		$count=count($arrhtml);
		for($i=0;$i<$count;$i++){
			if(preg_match($preg,$arrhtml[$i])){
				preg_match('/doc_header(1)/',$arrhtml[$i],$l_num);
				$resarr[$i]['value']=strip_tags($arrhtml[$i]);
				$resarr[$i]['flag']=strlen($l_num[1]);
				continue;
			}
			$resarr[$i]['value']=$arrhtml[$i];
			$resarr[$i]['flag']=0;
		}
		unset($arrhtml);
		return $resarr;
	}

	function splithtmlh2($html,$preg='/(<h3\s+class=\"?doc_header2\"?>.+?<\/h3>)/i'){
		$arrhtml=preg_split($preg,$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		$count=count($arrhtml);
		for($i=0;$i<$count;$i++){
			if(preg_match($preg,$arrhtml[$i])){
				preg_match('/doc_header(2)/i',$arrhtml[$i],$l_num);
				$resarr[$i]['value']=strip_tags($arrhtml[$i]);
				$resarr[$i]['flag']=strlen($l_num[1]);
				continue;
			}
			$resarr[$i]['value']=$arrhtml[$i];
			$resarr[$i]['flag']=0;
		}
		unset($arrhtml);
		return $resarr;
	}

	function getsections($section){
		$sectionlist=array();
		$secounts = count($section);
		for($i=0;$i<$secounts;$i++){
			if($section[$i]['flag'] == 1){
				$sectionlist[]=array('key'=>$i,'value'=>$section[$i]['value']);
			}
		}
		unset($section);
		return $sectionlist;
	}

	/*
	 * 非递归生成目录结构
	 * $categorys   所有目录数据
	 * $Pid 父类字段名称
	 * $cid 节点字段名称
	 * */
	function getSubclass($categorys,$pid,$cid,$RootID = 1){
		$Data = array();
		$Data = $categorys;
		$Output = Array();
		$i = 0;
		$len = Count($Data);
		if($RootID>1)
		{
			while($Data[$i][$pid] != $RootID && $i < $len) $i++;
		}
		$UpID = $RootID; //上个节点指向的分类父ID
		for($cnt = Count($Data); $i < $cnt;) //历遍整个分类数组
		{
			$j = 0; //初始化此次分类下子分类数据计数
			if ($UpID == $RootID) //在第一次循环时将所有一级分类保存到$Output这个数组中
			{
				while($Data[$i][$pid] == $UpID && $i < $len) //判断上一个节点是否为兄弟节点
				{
					$Output[$j] = $Data[$i]; //保存该节点到Output这个数组中
					$tmp[$Data[$i][$cid]] = &$Output[$j]; //并且将该节点ID在Output中的位置保存起来.
					$i++;
					$j++;
				}
			}
			else
			{
				while($Data[$i][$pid] == $UpID && $i < $len)
				{
					if($tmp[$UpID])
					{
						$tmp[$UpID]['subcat'][$j] = $Data[$i];
						$tmp[$Data[$i][$cid]] = &$tmp[$UpID]['subcat'][$j]; //保存该节点ID在Output中的位置
					}
					$i++;
					$j++;
				}
			}
			$UpID = $Data[$i][$pid];
		}
		return $Output;
	}

	function getRenZhenIcon($usertype){

		if($usertype == 1){
			$html = "<a href='".util::makeurl("help","cert")."' title='个人认证'><img src='".Hi::ini('SITE_URL/static')."/images/icon/i.png'/></a>";
		}else if($usertype == 2){
			$html = "<a href='".util::makeurl("help","cert")."' title='品牌认证'><img src='".Hi::ini('SITE_URL/static')."/images/icon/b.png' /></a>";
		}else if($usertype == 3){
			$html = "<a href='".util::makeurl("help","cert")."' title='热门妈妈'><img src='".Hi::ini('SITE_URL/static')."/images/icon/h.png' /></a>";
		}else if($usertype == 4){
			$html = "<a href='".util::makeurl("help","cert")."' title='名人妈妈'><img src='".Hi::ini('SITE_URL/static')."/images/icon/c.png' /></a>";
		}else if($usertype == 5){
			$html = "<a href='".util::makeurl("help","cert")."' title='专家'><img src='".Hi::ini('SITE_URL/static')."/images/icon/e.png' /></a>";
		}

		return $html;
	}

	function httpcws($data,$httpcws_url) {
		//echo "1.1.".memory_get_usage()."\r\n";
		if (!trim($data)) return "";
		$data = urlencode(iconv("UTF-8", "GBK", $data));
		$opts = array(
            'http'=>array(
                    'method'=>"POST",
                    'header'=>"Content-type: application/x-www-form-urlencoded\r\n".
                            "Content-length:".strlen($data)."\r\n" .
                            "Cookie: foo=bar\r\n" .
                            "\r\n",
                    'content' => $data,
		)
		);
		$context = stream_context_create($opts);
		$result =iconv("GBK", "UTF-8", file_get_contents($httpcws_url, false, $context));
		unset($context);
		unset($data);
		unset($opts);
		//echo "1.2.".memory_get_usage()."\r\n";
		return $result;
	}


	/*
	 * 优惠卷
	 * 生成11位upc条纹码
	 * $num 生成多少条记录
	 * 2010-11-26 fantom
	 * return bool
	 */

	static function coupons($num, $number = 11) {

		$arr = array();
		if ($number != 11) {
			$char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		}
		for ($i = 0; $i < $num; $i++) {
			$rand = null;
			for ($j = 0; $j <= $number; $j++) {
				if ($number == 11) {
					$rand .= rand(1, 9);
				} else {
					$rand .= $char[mt_rand(0, strlen($char) - 1)];
				}
			}

			if (in_array($rand, $arr)) {
				$i--;
				continue;
			}
			array_push($arr, $rand);
		}
		$com = '';

		return $arr;
	}

	/*
	 * 限制规则解析(展示数据)
	 * $rule string 限制规则入库数据
	 * $ruleArr string 配置
	 * return string 限制规则展示数据
	 */

	static function rulevalue($rule, $ruleArr) {
		if (!$rule) {
			return;
		}
		$result = $key = '';
		$tmp = array();
		$arr = explode(';', $rule);
		foreach ($arr as $value) {
			if (!$value) {
				continue;
			}
			preg_match('/([a-z_]+?)(=|>=|>|<=|<)(.*)/i', $value, $tmp); //匹配出字段名
			$keys = array_search($tmp[1], $ruleArr[1]);
			if (!is_int($keys)) {
				continue;
			}
			if (is_int($keys)) {
				$result .= str_replace($tmp[1], $ruleArr[0][$keys], $value) . "\n";
			}
			$key = '';
			$tmp = array();
		}
		return $result;
	}

	/*
	 * 限制规则解析（展示数据和处理数据）
	 * $rule string 限制规则入库数据
	 * $ruleArr array  规则配置文件
	 * return string 限制规则处理数据
	 */

	static function rulevaluer($rule, $ruleArr) {
		if (!$rule) {
			return;
		}
		$tmp = $result = array();
		$arr = explode(';', $rule);
		foreach ($arr as $value) {
			if (!$value) {
				continue;
			}
			preg_match('/([a-z_]+?)(=|>=|>|<=|<)(.*)/i', $value, $tmp); //匹配出字段名
			$keys = array_search($tmp[1], $ruleArr[1]);
			if (!is_int($keys)) {
				continue;
			}
			if($result['show'][$keys]){
				$result['show'][$keys+1]['value'] = str_replace($tmp[1], $ruleArr[0][$keys], $value);
				$result['show'][$keys+1]['field'] = "{$value};";
			}else{
				$result['show'][$keys]['value'] = str_replace($tmp[1], $ruleArr[0][$keys], $value);
				$result['show'][$keys]['field'] = "{$value};";
			}


			if ($ruleArr[3][$keys] == 'between') {
				$result['source'][$keys] = explode('--', $tmp[3]);
			} else if($ruleArr[3][$keys] == 'birth'){
				if($result[source][$keys])
				$keys += 1;
				$result['source'][$keys] = array(
            		'operator' => $tmp[2],
					'value' => $tmp[3]
				);
			}else {
				$result['source'][$keys] = explode(',', $tmp[3]);
			}
		}
		return $result;
	}
	
	/*
	 * 限制规则解析(数据解析)
	* $rule string 限制规则入库数据
	* $ruleArr array  规则配置文件
	* return string 限制规则处理数据
	*/
	
 	function rulevalues($rule, $ruleArr) {
		if (!$rule) {
			return;
		}
		$tmp = $result = $tmpvalue = array();
		$arr = explode(';', $rule);
		foreach ($arr as $value) {
			if (!$value) {
				continue;
			}
			preg_match('/([a-z_]+?)(=|>=|>|<=|<)(.*)/i', $value, $tmp); //匹配出字段名
			$keys = array_search($tmp[1], $ruleArr[1]);
			if (!is_int($keys)) {
				continue;
			}
			if ($ruleArr[3][$keys] == 'between') {
				if (strpos($tmp[2], ',')) {
					$tmpvalue = explode(',', $tmp[2]);
					foreach ($tmpvalue as $key => $values) {
						$tmpvalues = explode('--', $values);
						$result[$tmp[1]][$key . 'babay'] = array('>=' => $tmpvalues[0], '<=' => $tmpvalues[1]);
					}
				} else {
					$tmpvalue = explode('--', $tmp[2]);
					$result[$tmp[1]]['>='] = $tmpvalue[0];
					$result[$tmp[1]]['<='] = $tmpvalue[1];
				}
			} else {
				$result[$tmp[1]] = explode(',', $tmp[2]);
			}
		}
		return $result;
	}
	
	/*
	 * 申请规则限制
	*/
	
	function getTryRequestRule() {
		return array(
				array('所在地', '性别', '个人月收入', '家庭月收入','职业', '教育状况', '宝贝状态', '宝宝性别'),
				array('user_province', 'user_sex', 'user_inmoney','familyIncome', 'user_job', 'user_edu', 'babystatus','babysex'),
				array(2, 2, 2, 2, 2, 2, 1, 2),
				array('checkbox', 'checkbox', 'checkbox', 'checkbox', 'checkbox', 'checkbox', 'checkbox', 'checkbox'),
				array(
						array('北京', '上海', '天津', '重庆', '安徽', '福建', '甘肃', '广东', '广西', '贵州', '海南', '河北', '黑龙江', '河南', '香港', '湖北', '湖南', '江苏', '江西', '吉林', '辽宁', '澳门', '内蒙', '宁夏', '青海', '山东', '山西', '陕西', '四川', '台湾', '新疆', '西藏', '云南', '浙江'),
						array('男', '女'),
						array('1000以下', '1000-2000', '2000-3000', '3000-4000', '4000-5000', '5000-8000', '8000-12000', '12000-20000','20000以上'),
						array('2000以下', '2000-5000', '5000-8000', '8000-12000', '12000-20000', '20000-50000', '50000以上'),
						array('学生', '教师', '自由职业者', '公司职员', '私营企业主', '公务员', '军人','公司管理人员','其他'),
						array('小学及以下', '初中', '高中', '技校职高', '中专','大专','大本','硕士','博士以上'),
						array('已出生', '怀孕中'),
						array('男', '女')
				),
				array('所在地', '性别', '个人月收入', '职业', '教育状况', '宝贝状态', '宝贝年龄(单位为月)', '宝宝性别'),
		);
	}


}
