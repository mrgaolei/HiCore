<?php
!defined('HICORE_PATH') && exit('Access Denied');

/**
 * ���� HiCache_Exception �쳣
 *

 */

/**
 * HiCache_Exception �쳣��װ���еĻ������
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

