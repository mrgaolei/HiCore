<?php
!defined('HICORE_PATH') && exit('Access Denied');

/**
 * ���� Hi_FileNotFoundException �쳣

 */

/**
 * Hi_FileNotFoundException �쳣ָʾ�ļ�û���ҵ�����
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
