<?php
!defined('HICORE_PATH') && exit('Access Denied');

/**
 * ���� HiCache_File ��
 *


/**
 * HiCache_File ���ṩ���ļ�ϵͳ��������ݵķ���
 *

 */
class HiCache_File
{
	/**
	 * �Ƿ�����ʹ�û���
	 *
	 * @var boolean
	 */
	protected $_enabled = true;

	/**
	 * Ĭ�ϵĻ������
	 *
	 * -  life_time: ������Чʱ�䣨�룩��Ĭ��ֵ 900
     *    �������Ϊ 0 ��ʾ��������ʧЧ������Ϊ null ���ʾ����黺����Ч�ڡ�
	 *
     * -  serialize�� �Զ����л���ݺ���д�뻺�棬Ĭ��Ϊ true
     *    ���Ժܷ���Ļ��� PHP ����ֵ���������飩����Ҫ��һ�㡣
	 *
	 * -  encoding_filename�� ���뻺���ļ���Ĭ��Ϊ true
	 *    ����ID���ڷ��ļ����ַ���ô����Ի����ļ�����롣
     *
	 * -  cache_dir_depth: ����Ŀ¼��ȣ�Ĭ��Ϊ 0
	 *    ������ 1������ڻ���Ŀ¼�´�����Ŀ¼���滺���ļ���
	 *    ���Ҫд��Ļ����ļ����� 500 ����Ŀ¼�������Ϊ 1 ���� 2 ��Ϊ���ʡ�
	 *    ����и���ļ������Բ��ø��Ļ���Ŀ¼��ȡ�
     *
	 * -  cache_dir_umask: ��������Ŀ¼ʱ�ı�־��Ĭ��Ϊ 0700
     *
	 * -  cache_dir: ����Ŀ¼������ָ����
     *
	 * -  test_validity: �Ƿ��ڶ�ȡ��������ʱ���黺�����������ԣ�Ĭ��Ϊ true
     *
	 * -  test_method�� ���黺�����������Եķ�ʽ��Ĭ��Ϊ crc32
	 *    crc32 �ٶȽϿ죬���Ұ�ȫ��md5 �ٶ�������ɿ���strlen �ٶ���죬�ɿ����Բ
     *
	 * @var array
	 */
	protected $_default_policy = array
    (
		'life_time'         => 900,
		'serialize'         => true,
		'encoding_filename' => true,
		'cache_dir_depth'   => 0,
		'cache_dir_umask'   => 0700,
		'cache_dir'         => null,
		'test_validity'     => true,
		'test_method'       => 'crc32',
	);

	/**
	 * �̶�Ҫд�뻺���ļ�ͷ��������
	 *
	 * @var string
	 */
	static protected $_static_head = '<?php die(); ?>';

	/**
	 * �̶�ͷ���ĳ���
	 *
	 * @var int
	 */
	static protected $_static_head_len = 15;

	/**
	 * �����ļ�ͷ������
	 *
	 * @var int
	 */
	static protected $_head_len = 64;

	/**
	 * ���캯��
	 *
	 * @param Ĭ�ϵĻ������ $default_policy
	 */
	function __construct(array $default_policy = null)
	{
		if (!is_null($default_policy))
        {
			$this->_default_policy = array_merge($this->_default_policy, $default_policy);
		}

		if (empty($this->_default_policy['cache_dir']))
		{
		    $this->_default_policy['cache_dir'] = Hi::ini('runtime_cache_dir');
		}
	}

	/**
	 * д�뻺��
	 *
	 * @param string $id
	 * @param mixed $data
	 * @param array $policy
	 */
	function set($id, $data, array $policy = null)
	{
		if (!$this->_enabled) { return; }

		$policy = $this->_policy($policy);
		if ($policy['serialize'])
        {
			$data = serialize($data);
		}

		$path = $this->_path($id, $policy);

		// ���컺���ļ�ͷ��
		$head = self::$_static_head;
		$head .= pack('ISS', $policy['life_time'], $policy['serialize'], $policy['test_validity']);
		$head .= sprintf('% 8s', $policy['test_method']);
		$head .= str_repeat(' ', self::$_head_len - strlen($head));

		$content = $head;
		if ($policy['test_validity'])
        {
			// �������� 32 ���ֽ�д��������֤��������Ե���֤��
			$content .= $this->_hash($data, $policy['test_method']);
		}
		$content .= $data;
		unset($data);

		// д�뻺��
		file_put_contents($path, $content, LOCK_EX);
	}

	/**
	 * ��ȡ���棬ʧ�ܻ򻺴���ʧЧʱ���� false
	 *
	 * @param string $id
	 * @param array $policy
	 *
	 * @return mixed
	 */
	function get($id, array $policy = null)
	{
		if (!$this->_enabled) { return false; }

		$policy = $this->_policy($policy);

		// ������� life_time Ϊ null����ʾ���������������
		if (is_null($policy['life_time']))
        {
			$refresh_time = null;
		}
        else
        {
			$refresh_time = time();
		}

		$path = $this->_path($id, $policy, false);
		clearstatcache();
		if (!file_exists($path)) { return false; }

		// ��ȡ�ļ�ͷ��
		$fp = fopen($path, 'rb');
		if (!$fp) { return false; }
		flock($fp, LOCK_SH);

		$len = filesize($path);
		$mqr = get_magic_quotes_runtime();
		set_magic_quotes_runtime(0);

		// ͷ���� 32 ���ֽڴ洢�˸û���Ĳ���
		$head = fread($fp, self::$_head_len);
		$head = substr($head, self::$_static_head_len);
		$len -= self::$_head_len;
		$tmp = unpack('Il/Ss/St', substr($head, 0, 8));
		$policy['life_time'] = $tmp['l'];
		$policy['serialize'] = $tmp['s'];
		$policy['test_validity'] = $tmp['t'];
		$policy['test_method'] = trim(substr($head, 8, 8));
		do
        {
			// ��黺���Ƿ��Ѿ�����
			if (!is_null($refresh_time))
            {
				if (filemtime($path) <= $refresh_time - $policy['life_time'])
                {
					$hashtest = null;
					$data = false;
					break;
				}
			}

			// ��黺����ݵ�������
			if ($policy['test_validity'])
            {
				$hashtest = fread($fp, 32);
				$len -= 32;
			}

			if ($len > 0)
            {
				$data = fread($fp, $len);
			}
            else
            {
				$data = false;
			}
			set_magic_quotes_runtime($mqr);
		} while (false);

		flock($fp, LOCK_UN);
		fclose($fp);
		if ($data === false)
        {
			return false;
		}

		if ($policy['test_validity'])
        {
			$hash = $this->_hash($data, $policy['test_method']);
			if ($hash != $hashtest)
            {
				if (is_null($refresh_time))
                {
					// ������������ڵĻ����ļ�ûͨ����֤����ֱ��ɾ��
					unlink($path);
				}
                else
                {
					// ���������ļ�ʱ��Ϊ�Ѿ�����
					touch($path, time() - 2 * abs($policy['life_time']));
				}
				return false;
			}
		}

		if ($policy['serialize'])
        {
			$data = @unserialize($data);
		}

		return $data;
	}

	/**
	 * ɾ��ָ���Ļ���
	 *
	 * @param string $id
	 * @param array $policy
	 */
	function remove($id, array $policy = null)
	{
		$path = $this->_path($id, $this->_policy($policy), false);
		if (is_file($path)) { unlink($path); }
	}

	/**
	 * ȷ�������ļ���������Ҫ�Ĵμ�����Ŀ¼
	 *
	 * @param string $id
     * @param array $policy
     * @param boolean $mkdirs
	 *
	 * @return string
	 */
	protected function _path($id, array $policy, $mkdirs = true)
	{
		if ($policy['encoding_filename'])
        {
			$filename = 'cache_' . md5($id) . '.php';
		}
        else
        {
			$filename = 'cache_' . $id . '.php';
		}

		$root_dir = rtrim($policy['cache_dir'], '\\/');

        if (empty($root_dir))
        {
            throw new HiCache_Exception('cache_dir must be a directory. please check seting "runtime_cache_dir".');
        }

        $root_dir .= DIRECTORY_SEPARATOR;

		if ($policy['cache_dir_depth'] > 0 && $mkdirs)
        {
            $hash = md5($filename);
            $root_dir .= 'cache_';
            for ($i = 1; $i <= $policy['cache_dir_depth']; $i++)
            {
                $root_dir .= substr($hash, 0, $i) . DIRECTORY_SEPARATOR;
                if (is_dir($root_dir)) { continue; }
                mkdir($root_dir, $policy['cache_dir_umask']);
            }
        }

		return $root_dir . $filename;
	}

	/**
	 * ������Ч�Ĳ���ѡ��
	 *
	 * @param array $policy
	 * @return array
	 */
	protected function _policy(array $policy = null)
	{
		return !is_null($policy) ? array_merge($this->_default_policy, $policy) : $this->_default_policy;
	}

	/**
	 * �����ݵ�У����
	 *
	 * @param string $data
	 * @param string $type
	 * @return string
	 */
	protected function _hash($data, $type)
	{
		switch ($type)
        {
		case 'md5':
			return md5($data);
		case 'crc32':
			return sprintf('% 32d', crc32($data));
		default:
			return sprintf('% 32d', strlen($data));
		}
	}
}

