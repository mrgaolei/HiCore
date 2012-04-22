<?php
!defined('HICORE_PATH') && exit('Access Denied');

/**
 * 定义 Hi_ClassNotDefinedException 异常

 */

/**
 * Hi_ClassNotDefinedException 异常指示指定的文件中没有定义需要的类

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

