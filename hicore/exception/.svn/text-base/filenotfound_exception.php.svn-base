<?php
!defined('HICORE_PATH') && exit('Access Denied');

/**
 * 定义 Hi_FileNotFoundException 异常

 */

/**
 * Hi_FileNotFoundException 异常指示文件没有找到错误
 *

 */
class Hi_FileNotFoundException extends HiException
{
    public $required_filename;

    function __construct($filename)
    {
        $this->required_filename = $filename;
        parent::__construct('File "%s" not found.', $filename);
    }
}
