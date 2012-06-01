<?php

!defined('HICORE_PATH') && exit('Access Denied');

class Hidb2 {
	
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
		$result->setFetchMode(PDO::FETCH_ASSOC);
		$list = $result->fetchAll();
		return $list;
	}
	
	public function fetch_first($sql) {
		$query = $this->query($sql);
		return $query->fetch();
	}
	
	public function query($sql, $type = '') {
		return $this->mlink->query($sql);
	}
	
	public function affected_rows() {
		return -1;
	}
	
	public function error() {
		return $this->mlink->errorInfo();
	}
	
	public function errno() {
		return $this->mlink->errorCode();
	}
	
	public function insert_id() {
		return $this->mlink->lastInsertId();
	}
	
	public function close() {
		unset ($this->mlink);
	}
}

