<?php
!defined('HICORE_PATH') && exit('Access Denied');
/**
 * 定义 Q_IllegalFilenameException 异常
 *
 */

/**
 * Hi_IllegalFilenameException 异常指示存在无效字符的文件名
 *

 */
class Hi_IllegalFilenameException extends HiException
{
    public $required_filename;

    function __construct($filename)
    {
        $this->required_filename = $filename;
        parent::__construct('Security check: Illegal character in filename "%s".', $filename);
    }
}

