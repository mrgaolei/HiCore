<?php

!defined('HICORE_PATH') && exit('Access Denied');

class BaseModel {

    /**
     *
     * @var HiDB
     */
    protected $db;
    //protected $base;
    protected $_tablename;
    protected $_primarykey;
    protected $_validations = array();

    public function __construct(HiPDO $db) {
        $this->db = $db;
        // $this->base = $base;
		$tablepre = isset($this->_tablepre) ? $this->_tablepre : DB_TABLEPRE;
        $this->_tablename = $tablepre . $this->_tablename;
        
        if (!$this->_primarykey)
            $this->_primarykey = 'id';

        $this->validate();
        $this->initialize();
    }
    
    public function initialize(){}

    public function selecttable($where, $select = '*', $order = '') {
        $wheres = '1';
        $orders = '';
        foreach ($where as $k => $v) {
            $wheres .= ' AND `' . $k . '` = \'' . $v . '\'';
        }
        if ($order) {
            $orders = " ORDER BY $order";
        }
        $sql = "SELECT $select FROM ".$this->_tablename." WHERE $wheres $orders";
        return $this->db->fetch_by_sql($sql);
    }
    
    public function inserttable($insertdata = array(), $returninsertid = false, $replace = false, $ignore = true) {
        $keys = $values = array();
        $key = $value = "";
        foreach ($insertdata as $k => $v) {
            $keys[] = '' . $k . '';
            $values[] = $this->db->quote($v);
        }
        $key = implode(',', $keys);
        $value = implode(',', $values);
        if ($replace) {
        	$fun = "REPLACE";
        } else {
        	$fun = "INSERT";
        }
        $sql = "$fun INTO {$this->_tablename} ($key) VALUES ($value)";
        $result = $this->db->query($sql, '', $ignore);
        if (!$ignore && !$result) {
        	throw new Exception("Query Error With: $sql", 4414);
        }
        if ($returninsertid)
            return $this->db->insert_id("{$this->_tablename}_{$this->_primarykey}_seq");
    }

    public function updatetable($updatedate = array(), $where = array(), $addtype = false) {
        $sets = array();
        $set = "";
        foreach ($updatedate as $k => $v) {
        	if ($addtype) {
        		$oper = '' . $k . ' + ';
        	} else {
        		$oper = '';
        	}
            $sets[] = '' . $k . ' = ' . $oper .' \'' . $v . '\'';
        }
        $set = implode(',', $sets);
        $wheres = array();
        foreach ($where as $k => $v) {
            $wheres[] = '' . $k . ' = \'' . $v . '\'';
        }
        $where = implode(' AND ', $wheres);
        $sql = "UPDATE {$this->_tablename} SET $set WHERE $where";
        $query = $this->db->query($sql);
   		if (!$query) {
			$err = $this->db->error();
			throw new Exception($err[2], $err[1]);
		}
    }
    
    public function insertorupdatetable($updatedate = array(), $where = array(), $addtype = false) {
    	$data = array_merge($updatedate, $where);
    	$insertid = $this->inserttable($data, true, false, true);
    	if ($this->db->affected_rows() == -1) {
    		$this->updatetable($updatedate, $where, $addtype);
    	}
    }

    public function getAll($filters = array(), $pagesize = 0, $offset = 0, $order = array(), $select = '') {
        $where = " WHERE true ";
        if ($filters && is_array($filters)) {
            foreach ($filters as $k => $v) {
                if (is_string($v) || is_numeric($v)) {
                    $where .= " AND $k = '$v'";
                } elseif (is_array($v)) {
                    $where .= " AND $k IN (" . implode(',', $v) . ")";
                }
            }
        }
        $limit = "";
        if ($pagesize) {
            $limit = " LIMIT $offset, $pagesize";
        }
        $orderstr = "";
        if ($order) {
        	$orderstr = " ORDER BY";
        	foreach ($order as $k => $v) {
        		$orderstr .= " $k $v,";
        	}
        }
        $orderstr = trim($orderstr, ',');
        return $this->db->fetch_by_sql("SELECT * FROM {$this->_tablename} $where $orderstr $limit", $select);
    }

    public function getByPKID($id, $pk = null) {
        $pkfield = is_null($pk) ? $this->_primarykey : $pk;
        $stmt = $this->db->prepare("SELECT * FROM {$this->_tablename} WHERE $pkfield = :pkid");
        $stmt->execute(array(':pkid' => $id));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delByPKID($id, $pk = null) {
        $pkfield = is_null($pk) ? $this->_primarykey : $pk;
        $sql = "DELETE FROM `{$this->_tablename}` WHERE `$pkfield` = '$id'";
        return $this->db->query($sql);
    }

    /*
      ���ָ���ֶε�У�����
      $this->->addValidations(new ValidationRule('max_length', 5, '���ܳ���5���ַ�'),'field1');
      ��
      $this->->addValidations(V('max_length', 5, '���ܳ���5���ַ�'),'field1');

      ���Ҫһ������Ӷ����֤������Ҫʹ�ö�ά���飺

      $this->addValidations(array(
      V('min', 3, '����С��3'),
      V('max', 9, '���ܴ���9'),
      ),'field1');

      ���Ҫ���һ�� callback ������Ϊ��֤���򣬱�������д��
      $this->addValidations(V(array($obj, 'method_name'), $args, 'error_message'),'field1');


     */

    function addvalidations($validations, $field=null) {
        if ($validations instanceof ValidationRule) {
            $this->_validations[$field] = $validations;
        } elseif (is_array($validations)) {
            foreach ($validations as $v) {
                $this->_validations[$field] = $v;
            }
        } else {

            throw new HiException("args type of  validations must be array or be ValidationRule ");
        }
    }

    function getvalidation($field=null) {
        if (empty($field))
            return $this->_validations;
        else {
            if (in_array($field, $this->_validations)) {
                return $this->_validations[$field];
            } else {
                throw new HiException(" ValidationRule of field " . $field . " not config");
            }
        }
    }
    
    public function truncate() {
    	$this->db->query("TRUNCATE TABLE {$this->_tablename}");
    }

    public function makePrimaryID() {
        $int = time();
        $str = strval($int);
        $arr = array();
        for ($i = 0; $i < strlen($str); $i ++) {
            $arr[] = $str[$i];
        }
        shuffle($arr);
        $result = "";
        foreach ($arr as $a) {
            $result .= $a;
        }
        $result = intval($result);
        $one = $this->getByPKID($result);
        if ($one) {
            return $this->makePrimaryID();
        } else {
            return $result;
        }
    }

    function validate() {
        /*
          $this->addValidations(array(
          V('min', 3, '����С��3'),
          V('max', 9, '���ܴ���9'),
          ),'field1');
         */
    }

}
