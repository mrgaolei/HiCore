<?php
!defined('HICORE_PATH') && exit('Access Denied');
class string {

	function string() {
		die("Class string can not instantiated!");
	}
	
	static function substring($str, $start=0, $limit=12) {
		global $encoding;
		if('gbk'==strtolower($encoding)){
			$strlen=strlen($str);
			if ($start>=$strlen){
				return $str;
			}
			$clen=0;
			for($i=0;$i<$strlen;$i++,$clen++){
				if(ord(substr($str,$i,1))>0xa0){
					if ($clen>=$start){
						$tmpstr.=substr($str,$i,2);
					}
					$i++;
				}else{
					if ($clen>=$start){
						$tmpstr.=substr($str,$i,1);
					}
				}
				if ($clen>=$start+$limit){
					break;
				}
			}
			$str=$tmpstr;
		}else{
			$patten = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all($patten, $str, $regs);
			$v = 0; $s = '';
			for($i=0; $i<count($regs[0]); $i++){
				(ord($regs[0][$i]) > 129) ? $v += 2 : $v++;
				$s .= $regs[0][$i];
				if($v >= $limit * 2){
					break;
				}
			}
			$str=$s;
		}
		return $str;
	}
	
	static function hiconv($str,$to='',$from='',$force=false) {
		if (empty($str)) return $str;
		if(!preg_match( '/[\x80-\xff]/', $str)) return $str; // is contain chinese char
		global $encoding;
		if(empty($to)){
			if ('utf-8' == strtolower(Hi::ini('response_charset'))){
				return $str;
			}
			$to=$encoding;
		}
		if(empty($from)){
			$from = ('gbk'==strtolower($to)) ? 'utf-8':'gbk';
		}
		$to=strtolower($to);
		$from=strtolower($from);
		//$isutf8=preg_match( '/^([\x00-\x7f]|[\xc0-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xf7][\x80-\xbf]{3})+$/', $str );
		$re = strlen($str) > 6 ? '/([\xe0-\xef][\x80-\xbf]{2}){2}/' : '/[\xe0-\xef][\x80-\xbf]{2}/';
		$isutf8 = preg_match($re, $str);
		
		//$force = (substr($to, 0, 3) == 'utf') ? true : $force;
		
		if(!$force && $isutf8 && $to=='utf-8' ) return $str;
		if(!$force && !$isutf8 && $to=='gbk' ) return $str;
		
		if (function_exists('iconv')){
			$str = iconv($from, $to, $str);
		}else{
			require_once(HICMS_ROOT.'/lib/Chinese.class.php');
			$ch = new chinese($from,$to);
			if('utf-8'==$from){
				$str = addslashes($ch->convert(stripslashes($str)));
			}else{
				$str = $ch->convert($str);
			}
		}
		return $str;
	}

	static function hstrlen($str) {
		global $encoding;
		if('gbk'==strtolower($encoding)){
			$length=strlen($str);
		}else{
			$length=floor(2/3*strlen($str));
		}
		return $length;
	}

	static function hstrtoupper($str){
		if (is_array($str)){
			foreach ($str as $key => $val){
				$str[$key] = string::hstrtoupper($val);
			}
		}else{
			$i=0;
			$total = strlen($str);
			$restr = '';
			for ($i=0; $i<$total; $i++){
				$str_acsii_num = ord($str[$i]);
				if($str_acsii_num>=97 and $str_acsii_num<=122){
					$restr.=chr($str_acsii_num-32);
				}else{
					$restr.=chr($str_acsii_num);
				}
			}
		}
		return $restr;
	}
	
	static function hstrtolower($string){
		if (is_array($string)){
			foreach ($string as $key => $val){
				$string[$key] = string::hstrtolower($val);
			}
		}else{
			$string = strtolower($string);
		}
		return $string;
	}

	static function haddslashes($string, $force = 0) {
		if(!MAGIC_QUOTES_GPC || $force) {
			if(is_array($string)) {
				foreach($string as $key => $val) {
					$string[$key] = string::haddslashes($val, $force);
				}
			}else {
				$string = addslashes($string);
			}
		}
		return $string;
	}

	static function hstripslashes($string) {
		while(@list($key,$var) = @each($string)) {
			if ($key != 'argc' && $key != 'argv' && (strtoupper($key) != $key || ''.intval($key) == "$key")) {
				if (is_string($var)) {
					$string[$key] = stripslashes($var);
				}
				if (is_array($var))  {
					$string[$key] = string::hstripslashes($var);
				}
			}
		}
		return $string;
	}
	static function convercharacter($str){
		$str=str_replace('\\\r',"",$str);
		$str=str_replace('\\\n',"",$str);
		$str=str_replace('\n',"",$str);
		$str=str_replace('\r',"",$str);
		return $str;
	}

	static function getfirstletter($string) {
		global $encoding;
		if($encoding=='UTF-8'){
			$string=string::hiconv($string,'gbk','utf-8');
		}
		$dict=array(
			'a'=>0xB0C4,'b'=>0xB2C0,'c'=>0xB4ED,'d'=>0xB6E9,
			'e'=>0xB7A1,'f'=>0xB8C0,'g'=>0xB9FD,'h'=>0xBBF6,
			'j'=>0xBFA5,'k'=>0xC0AB,'l'=>0xC2E7,'m'=>0xC4C2,
			'n'=>0xC5B5,'o'=>0xC5BD,'p'=>0xC6D9,'q'=>0xC8BA,
			'r'=>0xC8F5,'s'=>0xCBF9,'t'=>0xCDD9,'w'=>0xCEF3,
			'x'=>0xD188,'y'=>0xD4D0,'z'=>0xD7F9,
			);
		$range=array(
			'a'=>0xB0A1,'b'=>0xB0C5,'c'=>0xB2C1,'d'=>0xB4EE,
			'e'=>0xB6EA,'f'=>0xB7A2,'g'=>0xB8C1,'h'=>0xB9FE,
			'j'=>0xBBF7,'k'=>0xBFA6,'l'=>0xC0AC,'m'=>0xC2E8,
			'n'=>0xC4C3,'o'=>0xC5B6,'p'=>0xC5BE,'q'=>0xC6DA,
			'r'=>0xC8BB,'s'=>0xC8F6,'t'=>0xCBFA,'w'=>0xCDDA,
			'x'=>0xCEF4,'y'=>0xD1B9,'z'=>0xD4D1,
			);
		$letter = substr($string, 0, 1);
		if($letter >= chr(0x81) && $letter <= chr(0xfe)) {
			$letter='*';
			$num = hexdec(bin2hex(substr($string, 0, 2)));
			foreach ($dict as $k=>$v){
				if($v>=$num && $range[$k]<=$num){
					$letter=$k;
					break;
				}
			}
			return $letter;
		}elseif((ord($letter)>64&&ord($letter)<91) || (ord($letter)>96&&ord($letter)<123) ){
			return $letter;
		}elseif($letter>='0' && $letter<='9'){
			return $letter;
		}else{
			return '*';
		}
	}
	
	static function stripspecialcharacter($string) {
		$string=trim($string);
		$string=str_replace("&","",$string);
		$string=str_replace("\'","",$string);
		$string=str_replace("'","",$string);
		$string=str_replace("&amp;amp;","",$string);
		$string=str_replace("&amp;quot;","",$string);
		$string=str_replace("\"","",$string);
		$string=str_replace("&amp;lt;","",$string);
		$string=str_replace("<","",$string);
		$string=str_replace("&amp;gt;","",$string);
		$string=str_replace(">","",$string);
		$string=str_replace("&amp;nbsp;","",$string);
		$string=str_replace("\\\r","",$string);
		$string=str_replace("\\\n","",$string);
		$string=str_replace("\n","",$string);
		$string=str_replace("\r","",$string);
		$string=str_replace("\r","",$string);
		$string=str_replace("\n","",$string);
		$string=str_replace("'","&#39;",$string);
		$string=nl2br($string);
		return $string;
	}

	static function convert_to_unicode($string){
		global $encoding;
		if($encoding=='GBK'){
			$string=string::hiconv($string,'utf-8','gbk');
		}
		$string=preg_replace("/([\\xc0-\\xff][\\x80-\\xbf]*)/e","' U8'.bin2hex( \"$1\" )",string::hstrtolower( $string ));
		if(strlen($string)<4){
			$string=' HDWIKI'.$string;
		}
		return $string;
	}

	static function stripscript($string){
		$pregfind=array("/<script.*>.*<\/script>/siU",'/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i');
		$pregreplace=array('','',);
		$string=preg_replace($pregfind,$pregreplace,$string);
		return $string;
	}
}
