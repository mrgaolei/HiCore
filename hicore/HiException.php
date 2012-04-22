<?php
!defined('HICORE_PATH') && exit('Access Denied');
/**
 * 定义 HiException 类
 *
 */

/**
 * HiException 是 HiPHP 所有异常的基础类
 *
 */
class HiException extends Exception
{
    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param int $code 错误代码
     */
    function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * 输出异常的详细信息和调用堆栈
     *
     * @code php
     * HiException::dump($ex);
     * @endcode
     */
    static function dump(HiException $ex)
    {
        $out = "exception '" . get_class($ex) . "'";
        if ($ex->getMessage() != '')
        {
            $out .= " with message '" . $ex->getMessage() . "'";
        }

        $out .= ' in ' . $ex->getFile() . ':' . $ex->getLine() . "\n\n";
        $out .= $ex->getTraceAsString();

        if (ini_get('html_errors'))
        {
            echo nl2br(htmlspecialchars($out));
        }
        else
        {
            echo $out;
        }
    }
}

