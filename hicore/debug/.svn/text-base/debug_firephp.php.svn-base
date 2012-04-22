<?php
!defined('HICORE_PATH') && exit('Access Denied');
/**
 * 定义 HiDebug_FirePHP 类

 */

/**
 * HiDebug_FirePHP 类提供对 FirePHP 的支持
 *
 */
abstract class HiDebug_FirePHP
{
    static protected $_firephp;
    static protected $_ver = '0.2';

    /**
     * 选择要使用的 FirePHP 扩展版本
     *
     * @param string $ver
     */
    static function ver($ver)
    {
        self::$_ver = $ver;
    }

    static function dump($vars, $label = null)
    {
        self::_firephp()->fb($vars, $label, FirePHP::LOG);
    }

    static function dumpTrace()
    {
    }

    static function assert($bool, $message = null)
    {
        if ($message)
        {
            $message = ' - ' . $message;
        }

        if ($bool)
        {
            self::_firephp()->fb('Assert TRUE' . $message, FirePHP::INFO);
        }
        else
        {
            self::_firephp()->fb('Assert FALSE' . $message, FirePHP::WARN);
        }
    }

    static function log($msg, $type = 'LOG')
    {
        self::_firephp()->fb($msg, $type);
    }

    /**
     * 返回  FirePHP 实例
     *
     * @return FirePHP
     */
    static protected function _firephp()
    {
        if (is_null(self::$_firephp))
        {
            $ver = self::$_ver;
            self::$_firephp = FirePHP::getInstance(true);
        }

        return self::$_firephp;
    }
}

