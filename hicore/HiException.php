<?php
!defined('HICORE_PATH') && exit('Access Denied');
/**
 * ���� HiException ��
 *
 */

/**
 * HiException �� HiPHP �����쳣�Ļ�����
 *
 */
class HiException extends Exception
{
    /**
     * ���캯��
     *
     * @param string $message ������Ϣ
     * @param int $code �������
     */
    function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * ����쳣����ϸ��Ϣ�͵��ö�ջ
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

