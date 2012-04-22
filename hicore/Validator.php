<?php

/**
 * ���� Validator ��


  /**
 * Validator �ṩ��һ����֤�������Լ�������֤�����Ľӿ�
 */
abstract class Validator {
    // ���й���ʧ��ʱ���������µĹ���
    const SKIP_ON_FAILED = 'skip_on_failed';
    // ������������
    const SKIP_OTHERS = 'skip_others';
    // ��֤ͨ��
    const PASSED = true;
    // ��֤ʧ��
    const FAILED = false;
    // ������й���
    const CHECK_ALL = true;

    /**
     * ���ػ�����
     *
     * @var array
     */
    static protected $_locale;

    /**
     * �õ���������ֵ֤
     *
     * ��֤ͨ������ true��ʧ�ܷ��� false��
     *
     * �÷���
     *
     * @code php
     * if (!Validator::validate($value, V('max', 5)))
     * {
     *     echo 'value ���ܴ��� 5';
     * }
     * @endcode
     *
     * $validation ��������֤����
     * ���ʹ�� Validator �Դ�����֤���򣬿���ʹ�����µ�д����
     *
     * @code php
     * Validator::validate($value, V('between', 1, 5));
     * @endcode
     *
     * 'between' ����֤��������֣���Ӧ�� Validator::validate_between() ������
     * ���Ҫʹ�� Validator ��������֤���������������еġ�validate_��ȥ����Ϊ��֤��������
     *
     * ���Ҫʹ���Զ������֤����������д����
     *
     * @code php
     * // ʹ��ĳ����ľ�̬��������֤����
     * Validator::validate($value, V(array('MyClass', 'myMethod'), $args));
     *
     * // ����ͬ��
     * Validator::validate($value, V('MyClass::myMethod', $args));
     *
     * // ʹ��ĳ������ķ�������֤����
     * Validator::validate($value, V(array($my_obj, 'myMethod), $args));
     * @endcode
     *
     * validate() �ĵ�һ��������Ҫ��֤��ֵ�����ڶ�����������֤����
     * �����֤������Ҫ����Ĳ���������ڵڶ������������ṩ��
     *
     * @param mixed $value Ҫ��֤��ֵ
     * @param mixed $validation ��֤���򼰲���
     *
     * @return boolean ��֤���
     */
    static function validate($value, ValidationRule $validationRule) {
        //$args = func_get_args();
        //unset($args[1]);
        $result = self::validateByArgs($validationRule->rule, array_merge(array($value), $validationRule->args));
        return (bool) $result;
    }

    /**
     * ��һ�������ֵ֤
     *
     * validateBatch() ������һ��ֵӦ��һ����֤���򣬲��������յĽ����
     * ��һ����֤������ֻҪ��һ����֤ʧ�ܣ����᷵�� false��
     * ֻ�е����й���ͨ��ʱ��validateBatch() �����Ż᷵�� true��
     *
     * �÷���
     *
     * @code php
     * $ret = Validator::validateBatch($value, array(
     *         V('is_int'),
     *         V('between', 2, 6),
     * ));
     * @endcode
     *
     * $validations ����������һ�����飬����������򣬼���֤������Ҫ�Ĳ�����
     * ÿ�����򼰲�������һ�����������顣
     *
     * ����ṩ�� $failed ����������֤ʧ�ܵĹ����洢�� $failed �����У�
     *
     * @code php
     * $failed = null;
     * $ret = Validator::validateBatch($value, $validations, true, $failed);
     *
     * dump($failed, '����û����֤ͨ���Ĺ���');
     * @endcode
     *
     * @param mixed $value Ҫ��֤��ֵ
     * @param array $validations �ɶ����֤���򼰲�����ɵ�����
     * @param boolean $check_all �Ƿ������й���
     * @param mixed $failed ������֤ʧ�ܵĹ�����
     *
     * @return boolean ��֤���
     */
    static function validateBatch($value, array $validations, $check_all = true, & $failed = null) {
        $result = true;
        $failed = array();
        foreach ($validations as $v) {
            //$vf = $v[0];
            //$v[0] = $value;
            $ret = self::validateByArgs($v->rule, array_merge(array($value), $v->args));

            // �������µ���֤����
            if ($ret === self::SKIP_OTHERS) {
                return $result;
            }

            if ($ret === self::SKIP_ON_FAILED) {
                $check_all = false;
                continue;
            }

            if ($ret)
                continue;

            $failed[] = $v;
            $result = $result && $ret;

            if (!$result && !$check_all)
                return false;
        }

        return (bool) $result;
    }

    /**
     * �õ������򼰸��Ӳ�����ֵ֤
     *
     * validateByArgs() ������ validate() ����������ͬ��ֻ�ǲ�����ʽ��ͬ��
     *
     * validateByArgs() �����ĵ�һ����������֤����
     * �ڶ����������ǰ�������ֵ֤���ڣ�Ҫ���ݸ���֤����Ĳ�����
     *
     * ���磺
     *
     * @code php
     * // validate() ��д��
     * Validator::validate($value, 'max', 6);
     *
     * // validateByArgs() ��д��
     * Validator::validateByArgs('max', array($value, 6));
     * @endcode
     *
     * @param mixed $validation ��֤����
     * @param array $args Ҫ���ݸ���֤����Ĳ���
     *
     * @return boolean ��֤���
     */
    static function validateByArgs($validation, array $args) {
        static $internal_funcs;

        if (is_null($internal_funcs)) {
            $internal_funcs = array('between', 'equal', 'greater_or_equal',
                'greater_than', 'is_alnum', 'is_alnumu', 'is_alpha', 'is_ascii',
                'is_binary', 'is_cntrl', 'is_date', 'is_datetime', 'is_digits',
                'is_domain', 'is_email', 'is_float', 'is_graph', 'is_int',
                'is_ipv4', 'is_lower', 'is_octal', 'is_print', 'is_punct',
                'is_time', 'is_type', 'is_upper', 'is_whitespace', 'is_xdigits',
                'less_or_equal', 'less_than', 'max', 'strlen', 'max_length', 'min',
                'min_length', 'not_empty', 'not_equal', 'not_null', 'not_same',
                'regex', 'same', 'skip_empty', 'skip_null', 'skip_on_failed');
            $internal_funcs = array_flip($internal_funcs);
        }

        if ($validation == 'equal') {
            if (strpos($args[1], "#") !== false) {
             
                 return true;
            }
        }

        if ($validation == 'ajax') {
            return true;
        }

        // Validator �����֤����
        if (!is_array($validation) && isset($internal_funcs[$validation])) {

            $result = call_user_func_array(array(__CLASS__, 'validate_' . $validation), $args);
        } elseif (is_array($validation) || function_exists($validation)) {
            $result = call_user_func_array($validation, $args);
        } elseif (strpos($validation, '::')) {
            $result = call_user_func_array(explode('::', $validation), $args);
        } else {
            throw new HiException($validation . " not define ");
        }
        return $result;
    }

    /**
     * ���Ϊ�գ����ַ������� null�������������µ���֤
     *
     * @return mixed $value
     *
     * @return boolean
     */
    static function validate_skip_empty($value) {
        return (strlen($value) == 0) ? self::SKIP_OTHERS : true;
    }

    /**
     * ���ֵΪ NULL�����������µ���֤
     *
     * @return mixed $value
     *
     * @return boolean
     */
    static function validate_skip_null($value) {
        return (is_null($value)) ? self::SKIP_OTHERS : true;
    }

    /**
     * �������������֤���������������������֤
     *
     * @return boolean
     */
    static function validate_skip_on_failed() {
        return self::SKIP_ON_FAILED;
    }

    /**
     * ʹ��������ʽ������֤
     *
     * @param mixed $value
     * @param string $regxp
     *
     * @return boolean
     */
    static function validate_regex($value, $regxp) {
        return preg_match($regxp, $value) > 0;
    }

    /**
     * �Ƿ����ָ��ֵ
     *
     * @param mixed $value
     * @param mixed $test
     *
     * @return boolean
     */
    static function validate_equal($value, $test) {
        return $value == $test && strlen($value) == strlen($test);
    }

    /**
     * ������ָ��ֵ
     *
     * @param mixed $value
     * @param mixed $test
     *
     * @return boolean
     */
    static function validate_not_equal($value, $test) {
        return $value != $test || strlen($value) != strlen($test);
    }

    /**
     * �Ƿ���ָ��ֵ��ȫһ��
     *
     * @param mixed $value
     * @param mixed $test
     *
     * @return boolean
     */
    static function validate_same($value, $test) {
        return $value === $test;
    }

    /**
     * �Ƿ���ָ��ֵ����ȫһ��
     *
     * @param mixed $value
     * @param mixed $test
     *
     * @return boolean
     */
    static function validate_not_same($value, $test) {
        return $value !== $test;
    }

    /**
     * ��֤�ַ�������
     *
     * @param string $value
     * @param int $len
     *
     * @return boolean
     */
    static function validate_strlen($value, $len) {
        return strlen($value) == (int) $len;
    }

    /**
     * ��С����
     *
     * @param mixed $value
     * @param int $len
     *
     * @return boolean
     */
    static function validate_min_length($value, $len) {
        return strlen($value) >= $len;
    }

    /**
     * ��󳤶�
     *
     * @param mixed $value
     * @param int $len
     *
     * @return boolean
     */
    static function validate_max_length($value, $len) {
        return strlen($value) <= $len;
    }

    /**
     * ��Сֵ
     *
     * @param mixed $value
     * @param int|float $min
     *
     * @return boolean
     */
    static function validate_min($value, $min) {
        return $value >= $min;
    }

    /**
     * ���ֵ
     *
     * @param mixed $value
     * @param int|float $max
     *
     * @return boolean
     */
    static function validate_max($value, $max) {
        return $value <= $max;
    }

    /**
     * ������ֵ֮��
     *
     * @param mixed $value
     * @param int|float $min
     * @param int|float $max
     * @param boolean $inclusive �Ƿ���� min/max ����
     *
     * @return boolean
     */
    static function validate_between($value, $min, $max, $inclusive = true) {
        if ($inclusive) {
            return $value >= $min && $value <= $max;
        } else {
            return $value > $min && $value < $max;
        }
    }

    /**
     * ����ָ��ֵ
     *
     * @param mixed $value
     * @param int|float $test
     *
     * @return boolean
     */
    static function validate_greater_than($value, $test) {
        return $value > $test;
    }

    /**
     * ���ڵ���ָ��ֵ
     *
     * @param mixed $value
     * @param int|float $test
     *
     * @return boolean
     */
    static function validate_greater_or_equal($value, $test) {
        return $value >= $test;
    }

    /**
     * С��ָ��ֵ
     *
     * @param mixed $value
     * @param int|float $test
     *
     * @return boolean
     */
    static function validate_less_than($value, $test) {
        return $value < $test;
    }

    /**
     * С�ڵ�¼ָ��ֵ
     *
     * @param mixed $value
     * @param int|float $test
     *
     * @return boolean
     */
    static function validate_less_or_equal($value, $test) {
        return $value <= $test;
    }

    /**
     * ��Ϊ null
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_not_null($value) {
        return!is_null($value);
    }

    /**
     * ��Ϊ��
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_not_empty($value) {
        return!empty($value);
    }

    /**
     * �Ƿ����ض�����
     *
     * @param mixed $value
     * @param string $type
     *
     * @return boolean
     */
    static function validate_is_type($value, $type) {
        return gettype($value) == $type;
    }

    /**
     * �Ƿ�����ĸ������
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_alnum($value) {
        return ctype_alnum($value);
    }

    /**
     * �Ƿ�����ĸ
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_alpha($value) {
        return ctype_alpha($value);
    }

    /**
     * �Ƿ�����ĸ�����ּ��»���
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_alnumu($value) {
        return preg_match('/[^a-zA-Z0-9_]/', $value) == 0;
    }

    /**
     * �Ƿ��ǿ����ַ�
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_cntrl($value) {
        return ctype_cntrl($value);
    }

    /**
     * �Ƿ�������
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_digits($value) {
        return ctype_digit($value);
    }

    /**
     * �Ƿ��ǿɼ����ַ�
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_graph($value) {
        return ctype_graph($value);
    }

    /**
     * �Ƿ���ȫСд
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_lower($value) {
        return ctype_lower($value);
    }

    /**
     * �Ƿ��ǿɴ�ӡ���ַ�
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_print($value) {
        return ctype_print($value);
    }

    /**
     * �Ƿ��Ǳ�����
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_punct($value) {
        return ctype_punct($value);
    }

    /**
     * �Ƿ��ǿհ��ַ�
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_whitespace($value) {
        return ctype_space($value);
    }

    /**
     * �Ƿ���ȫ��д
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_upper($value) {
        return ctype_upper($value);
    }

    /**
     * �Ƿ���ʮ��������
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_xdigits($value) {
        return ctype_xdigit($value);
    }

    /**
     * �Ƿ��� ASCII �ַ�
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_ascii($value) {
        return preg_match('/[^\x20-\x7f]/', $value) == 0;
    }

    /**
     * �Ƿ��ǵ����ʼ���ַ
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_email($value) {
        //return preg_match('/^[a-z0-9]+[._\-\+]*@([a-z0-9]+[-a-z0-9]*\.)+[a-z0-9]+$/i', $value);
        return preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value);
    }

    /**
     * �Ƿ������ڣ�yyyy/mm/dd��yyyy-mm-dd��
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_date($value) {
        if (strpos($value, '-') !== false) {
            $p = '-';
        } elseif (strpos($value, '/') !== false) {
            $p = '\/';
        } else {
            return false;
        }

        if (preg_match('/^\d{4}' . $p . '\d{1,2}' . $p . '\d{1,2}$/', $value)) {
            $arr = explode($p, $value);
            if (count($arr) < 3)
                return false;

            list($year, $month, $day) = $arr;
            return checkdate($month, $day, $year);
        }
        else {
            return false;
        }
    }

    /**
     * �Ƿ���ʱ�䣨hh:mm:ss��
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_time($value) {
        $parts = explode(':', $value);
        $count = count($parts);
        if ($count != 2 || $count != 3) {
            return false;
        }
        if ($count == 2) {
            $parts[2] = '00';
        }
        $test = @strtotime($parts[0] . ':' . $parts[1] . ':' . $parts[2]);
        if ($test === - 1 || $test === false || date('H:i:s') != $value) {
            return false;
        }

        return true;
    }

    /**
     * �Ƿ������� + ʱ��
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_datetime($value) {
        $test = @strtotime($value);
        if ($test === false || $test === - 1) {
            return false;
        }
        return true;
    }

    /**
     * �Ƿ�������
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_int($value) {
        if (is_null(self::$_locale)) {
            self::$_locale = localeconv();
        }

        $value = str_replace(self::$_locale['decimal_point'], '.', $value);
        $value = str_replace(self::$_locale['thousands_sep'], '', $value);

        if (strval(intval($value)) != $value) {
            return false;
        }
        return true;
    }

    /**
     * �Ƿ��Ǹ�����
     *
     * @param mixed $value
     */
    static function validate_is_float($value) {
        if (is_null(self::$_locale)) {
            self::$_locale = localeconv();
        }

        $value = str_replace(self::$_locale['decimal_point'], '.', $value);
        $value = str_replace(self::$_locale['thousands_sep'], '', $value);

        if (strval(floatval($value)) != $value) {
            return false;
        }
        return true;
    }

    /**
     * �Ƿ��� IPv4 ��ַ����ʽΪ a.b.c.h��
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_ipv4($value) {
        $test = @ip2long($value);
        return $test !== - 1 && $test !== false;
    }

    /**
     * �Ƿ��ǰ˽�����ֵ
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_octal($value) {
        return preg_match('/0[0-7]+/', $value);
    }

    /**
     * �Ƿ��Ƕ�������ֵ
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_binary($value) {
        return preg_match('/[01]+/', $value);
    }

    /**
     * �Ƿ��� Internet ����
     *
     * @param mixed $value
     *
     * @return boolean
     */
    static function validate_is_domain($value) {
        return preg_match('/[a-z0-9\.]+/i', $value);
    }

}

?>
