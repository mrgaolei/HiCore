<?php
interface HiDB {
	
	public function select($sql, $keyfield = '');
	
	public function starttransaction();
	
	public function commit();
	
	public function rollback() ;
	
	public function autocommit($auto = true);
	
	public function fetch_first($sql);
	
	public function fetch_by_field($table, $field, $value, $select_fields='*');
	
	public function fetch_by_sql($sql, $select = '');
	
	public function update_field($table, $field, $value, $where);
	
	public function fetch_total($table, $where='1');
	
	public function query($sql, $type = '', $ignore = false);
	
	public function affected_rows();
	
	public function error();
	
	public function errno();
	
	public function insert_id($name = null);
	
	
}

