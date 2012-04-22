<?php
!defined('HICORE_PATH') && exit('Access Denied');

/**
 * ���� Hi_ClassNotDefinedException �쳣

 */

/**
 * Hi_ClassNotDefinedException �쳣ָʾָ�����ļ���û�ж�����Ҫ����

 */
class Hi_ClassNotDefinedException extends HiException
{
    public $class_name;
    public $filename;

    function __construct($class_name, $filename)
    {
        $this->class_name = $class_name;
        $this->filename = $filename;
        parent::__construct('Class "%s" not defined in file "%s".', $class_name, $filename);
    }
}

