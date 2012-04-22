<?php
!defined('HICORE_PATH') && exit('Access Denied');
/**
 * ���� HiDebug_FirePHP ��

 */

/**
 * HiDebug_FirePHP ���ṩ�� FirePHP ��֧��
 *
 */
abstract class HiDebug_FirePHP
{
    static protected $_firephp;
    static protected $_ver = '0.2';

    /**
     * ѡ��Ҫʹ�õ� FirePHP ��չ�汾
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
     * ����  FirePHP ʵ��
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

