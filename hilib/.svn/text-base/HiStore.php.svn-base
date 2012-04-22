<?php

/**
 * Description of HiStore
 * Last update at 2010-9-6 14:14
 *
 * @author mrgaolei
 */
class HiStore {
    private $apiurl;
    private $custname;
    private $pubkey;

    public function  __construct($apiurl, $custname, $pubkey) {
        $this->apiurl = $apiurl;
        $this->custname = $custname;
        $this->pubkey = $pubkey;
    }
    
    public function fetchimages($value = 1, $type = 'a', $page = 1, $pagesize = 10) {
    	$verify = md5($this->custname . $type . $value . $this->pubkey);
    	$form = array(
    		'custname' => $this->custname,
    		'verify' => $verify,
    		'type' => $type,
    		'value' => $value,
    		'page' => $page,
    		'pagesize' => $pagesize,
    	);
    	$result = $this->formpost($form, $this->apiurl.'fetchimages/');
    	return $result;
    }
    
    /**
     * 
     * �õ��û�������ģ��
     */
    public function fetchtemplate() {
    	$verify = md5($this->custname . $this->pubkey);
    	$form = array(
    		'verify' => $verify,
    		'custname' => $this->custname,
    	);
    	$result = json_decode($this->formpost($form, $this->apiurl.'fetchtemplate/'), true);
    	foreach ($result as $k => $v) {
    		$v['usage'] = iconv('utf-8', 'gbk', $v['usage']);
    		$result[$k] = $v;
    	}
    	return $result;
    }
    
    /**
     * 
     * �õ�ģ������з���
     * @param int $tid
     */
    public function fetchplan($tid) {
    	$verify = md5($this->custname. $tid . $this->pubkey);
    	$form = array(
    		'verify' => $verify,
    		'custname' => $this->custname,
    		'tid' => $tid,
    	);
    	$result = $this->formpost($form, $this->apiurl.'fetchplan/');
    	
    	return $result;
    }
    
    public function customupload($file, $tid = 1, $thumb = 1, $thumbwidth = 320, $thumbheight = 240, $watermark = 1) {
    	$verify = md5(filesize($file).$this->custname.$this->pubkey.$thumbwidth.$thumbheight);
    	$form = array(
    		'verify' => $verify,
    		'custname' => $this->custname,
    		'tid' => $tid,
    		'file' => "@$file",
    		'thumb' => $thumb,
    		'thumbwidth' => $thumbwidth,
    		'thumbheight' => $thumbheight,
    		'watermark' => $watermark,
    	);
    	return $this->formpost($form, $this->apiurl.'customupload/');
    }

    public function upload($file, $tid = 1, $description = null) {
        $verify = md5(filesize($file).$this->custname.$this->pubkey);
        $form = array(
            'verify' => $verify,
            'custname' => $this->custname,
            'tid' => $tid,
            'file' => "@$file",
        );
        if (!is_null($description)) {
        	$form['description'] = $description;
        }
        return $this->formpost($form, $this->apiurl.'upload/');
    }

    public function del($id, $file) {
        $verify = md5($file . $this->custname. $this->pubkey);
        $form = array(
            'verify' => $verify,
            'custname' => $this->custname,
            'id' => $id,
        );
        return $this->formpost($form, $this->apiurl.'del/');
    }

    public static function formpost($data, $url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
}
