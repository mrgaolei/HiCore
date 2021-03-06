<?php

!defined('HICORE_PATH') && exit('Access Denied');

class HiPDO {
	
	var $mlink;
	
	public function __construct($dsn, $username, $passwd, $options, $usetrans = true, $autocommit = false) {
		$this->mlink = new PDO($dsn, $username, $passwd, $options);
		if ($usetrans) {
			$this->starttransaction();
			$this->autocommit($autocommit);
		}
	}
	
	public function select($sql, $keyfield = '') {
		return $this->fetch_by_sql($sql, $keyfield);
	}
	
	public function starttransaction() {
		$this->mlink->beginTransaction();
	}
	
	public function commit() {
		$this->mlink->commit();
	}
	
	public function rollback() {
		$this->mlink->rollBack();
	}
	
	public function autocommit($auto = true) {
		
	}
	
	public function fetch_by_sql($sql, $select = '') {
		$result = $this->query($sql);
		if (!$result) {
			$err = $this->error();
			throw new Exception($err[2], $err[1]);
		}
		$result->setFetchMode(PDO::FETCH_ASSOC);
		$list = $result->fetchAll();
		if ($select) {
			$return = array();
			foreach ($list as $row) {
				if ($row[$select]) {
					$return[$row[$select]] = $row;
				}
			}
			return $return;
		} else {
			return $list;
		}
	}
	
	public function fetch_first($sql) {
		$query = $this->query($sql);
		if (!$query) {
			$err = $this->error();
			throw new Exception($err[2], $err[1]);
		}
		return $query->fetch();
	}
	
	public function query($sql, $type = '', $ignore = false) {
		return $this->mlink->query($sql);
	}
	
	public function prepare($statement, $driver_options = array()) {
		return $this->mlink->prepare($statement, $driver_options);
	}
	
	public function quote ($string, $parameter_type = null) {
		return $this->mlink->quote($string, $parameter_type);
	}
	
	public function affected_rows() {
		throw new Exception("PDO cann't use this function to return affected_rows, you can use query() to know them.", 445);
		return -1;
	}
	
	public function error() {
		return $this->mlink->errorInfo();
	}
	
	public function errno() {
		return $this->mlink->errorCode();
	}
	
	public function insert_id($name = null) {
		if ($this->mlink->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql' && is_null($name))
		{
			throw new Exception("pgsql need a seq_name to return lastInsertId()", 444);
		}
		return $this->mlink->lastInsertId($name);
	}
	
	public function close() {
		unset ($this->mlink);
	}
}

