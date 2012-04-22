<?php
!defined('HICORE_PATH') && exit('Access Denied');

/**
 * 定义 HiCache_Exception 异常
 *

 */

/**
 * HiCache_Exception 异常封装所有的缓存错误
 *

 */
class HiCache_Exception extends HiException
{
    public $filename;

    function __construct($msg, $filename = null)
    {
        $this->filename = $filename;
        parent::__construct($msg, $filename);
    }
}

