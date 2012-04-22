<?php

!defined('HICORE_PATH') && exit('Access Denied');

class TqUtil extends util {

    static function getTopics($msg)
    {
    	$r = preg_match_all('/#([^#]*)#/', $msg, $matchs);
    	return $matchs[1];
    }
    
    static function getDomain($url)
    {
    	$url .= '/';
    	if (strtolower(substr($url, 0, 7)) == 'http://') preg_match('/http:\/\/([^\/]+)\//i', $url, $r);
    	else preg_match('/([^\/]+)\//i', $url, $r);
    	return $r[1];
    }
    
	/**
     * 取得一组 id 数组或逗号分隔的字符串
     * @param array $data 数组源
     * @param string $idColumn 要取得的字段
     * @param bool $returnStr 是否返回字符串（默认为返回数组）
     * @param bool $colislist $idColumn相应字段内容是否为逗号分隔的字符串
     */
    static function getIds($data = array(), $idColumn = 'id', $returnStr = false, $colislist = false)
    {
    	$idres = array();
    	if (is_array($data)) {
    	foreach ($data as $k => $v) {
    		$idstmp = array();
    		if ($v[$idColumn]) {
    			if ($colislist) {
    				$idstmp = explode(',', $v[$idColumn]);
    				$idres = array_merge($idres, $idstmp);
    			} else {
    				$idres[] = $v[$idColumn];
    			}
    		}
    		unset($data[$k]);
    	}
    	}
    	$idres = array_unique($idres);
    	if ($returnStr) return implode(',', $idres);
    	return $idres;
    }
    
	static function iconv($pramas, $incharset = 'gbk', $outcharset = 'utf-8') {
		return $pramas;
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
    
	static function date($time, $type = 3, $friendly=0) {
        if (!$time) return '';
        $now = time();
        if (!$type) {
        	$format[] = 'n-j';
        } else {
        	$format[] = $type & 2 ? 'Y-n-j' : '';
        	$format[] = $type & 1 ? 'H:i' : '';
        }
        $timestring = date(implode(' ', $format), $time);
        if ($friendly) {
            $dtime = $now - $time;
            $dday = intval(date('z', $now)) - intval(date('z', $time));
            $dyear = intval(date('Y', $now)) - intval(date('Y', $time));
            if ($dtime < 0) {
                
            } elseif ($dtime < 60) {
                $timestring = $dtime . '秒前';
            } elseif ($dtime < 3600) {
                $timestring = floor($dtime / 60) . '分钟前';
            } elseif ($dtime < 86400) {
                $timestring = floor($dtime / 3600) . '小时前';
            } elseif ($dday < 2 && $dyear < 1) {
                $timestring = '昨天';
            } elseif ($dday < 3 && $dyear < 1) {
                $timestring = '前天';
            } elseif ($dday < 7 && $dyear < 1) {
                $timestring = $dday . '天前';
            }
        }
        return $timestring;
    }
    
    /**
     * 数组按指定字段排序
     * @param $array
     * @param $column
     * @param $isdesc
     */
    static function resort($array, $column, $isdesc = true) {
    	$orderby = array();
    	foreach ($array as $v) {
    		$orderby[] = $v[$column];
    	}
    	$order = $isdesc ? SORT_DESC : SORT_ASC;
    	array_multisort($orderby, $order, SORT_REGULAR, $array);
    	return $array;
    }
    
    /**
     * 数组$array的$column按照$orderby排序
     * @param array $array
     * @param string $column
     * @param array $orderby
     */
    static function resortByArray($array, $column, $orderby) {
    		$array = self::resetKey($array, $column);
    		$data = array();
    		foreach ($orderby as $id) {
    			if ($array[$id]) {
    				$data[] = $array[$id];
    				unset($array[$id]);
    			}
    		}
    		$array = array_values($array);
    		$array = array_merge($data, $array);
    		return $array;
    }
    
    /**
     * 数组按指定字段去重
     * @param $array
     * @param $column
     */
    static function unique($array, $column) {
    	$ids = array();
    	foreach ($array as $k => $v) {
    		if (in_array($v[$column], $ids)) unset($array[$k]);
    		$ids[] = $v[$column];
    	}
    	return $array;
    }
    
    /**
     * 将数组$array的key重置为$column的值
     * @param $array
     * @param $column
     */
    static function resetKey($array, $column) {
    	$r = array();
    	foreach ($array as $v) {
    		$r[$v[$column]] = $v;
    	}
    	return $r;
    }
    
    /**
     * 过滤数组
     * @param $array
     * @param $column 指定的key
     * @param $val 判断值
     * @param $unset 符合条件的删除
     */
    static function filterByKey($array, $column, $val = 0, $unset = true) {
    	$res = array();
    	foreach ($array as $k => $v) {
    		if ($v[$column] == $val) {
    			if ($unset) unset($array[$k]);
    			else $res[] = $v;
    		}
    	}
    	if ($unset) $res = $array;
    	return $res;
    }
    
    static function getFileType($url){
    	$url = parse_url($url);
    	if ($fp = @fsockopen($url['host'], empty($url['port'])?80:$url['port'], $error)) {
    		fputs($fp, "GET ".(empty($url['path'])?'/':$url['path'])." HTTP/1.1\r\n");
    		fputs($fp, "Host:$url[host]\r\n\r\n");
    		while(!feof($fp)){
    			$tmp = fgets($fp);
    			if(trim($tmp) == '') break;
    			elseif(preg_match('/Content-Type:(.*)/si',$tmp,$arr)) return trim($arr[1]);
    		}
    	}
    	return null;
    }
    
    static function mbsubstr($str, $start = 0, $length = 20, $fix = '...') {
    	$strlen = mb_strlen($str, 'utf8');
    	if ($strlen <= $length) $fix = '';
    	return mb_substr($str, $start, $length, 'utf8') . $fix;
    }
    
    static function mkdaterange($type = 'week', $time = 0) {
    	if (!$time) $time = time();
    	switch ($type) {
    		case 'hour' :
    			$op = mktime(date('H', $time), 0, 0, date('m', $time), date('d', $time), date('Y', $time));
    			$ed = mktime(date('H', $time), 59, 59, date('m', $time), date('d', $time), date('Y', $time));
    			break;

    		case 'day' :
    			$op = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
    			$ed = mktime(23, 59, 59, date('m', $time), date('d', $time), date('Y', $time));
    			break;

    		case 'week' :
    			$op = mktime(0, 0, 0, date('m', $time), date('d', $time) - date('w', $time) + 1, date('Y', $time));
    			$ed = mktime(23, 59, 59, date('m', $time), date('d', $time) - date('w', $time) + 7, date('Y', $time));
    			break;

    		case 'month' :
    			$op = mktime(0, 0, 0, date('m', $time) - 1, 1, date('Y', $time));
    			$ed = mktime(23, 59, 59, date('m', $time), 0, date('Y', $time));
    			break;
    			 
    		default :
    			$op = mktime(0, 0, 0, date('m', $time), date('d', $time) - date('w', $time) + 1, date('Y', $time));
    			$ed = mktime(23, 59, 59, date('m', $time), date('d', $time) - date('w', $time) + 7, date('Y', $time));
    			break;
    	}
    	return array('op'=>$op, 'ed'=>$ed);
    }
    
    static function str2arr($str, $delimiter = ',', $filterNull = true) {
    	$str = trim($str, $delimiter);
    	$arr = explode($delimiter, $str);
    	if ($filterNull) $arr = self::filterNull($arr);
    	return $arr;
    }
    
    /**
    * 将数组$array按$column的值分组
    * @param $array
    * @param $column
    */
    static function colKey($array, $column) {
    	$r = array();
    	foreach ($array as $v) {
    		if ($v) {
    			if (!key_exists($v[$column], $r)) $r[$v[$column]] = array();
    			$r[$v[$column]][] = $v;
    		}
    	}
    	return $r;
    }

}