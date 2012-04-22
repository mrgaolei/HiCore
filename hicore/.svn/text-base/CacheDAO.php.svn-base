<?php

class CacheDAO extends ShardDAO {
	
	protected $cacheType = 'memcached';
	private $cacheTypes = array('memcached', 'memcachedb');
	
	public function __construct() {
		parent::__construct();
		if (!in_array($this->cacheType, $this->cacheTypes)) $this->throwException(9901, 'Wrong CacheHandle');
		if ($this->cacheType == 'memcachedb') $this->throwException(9903, 'The CacheHandle of memcachedb is forbidden now');
		if (!$this->_primarykey) $this->throwException(9902, 'Missing primarykey config');
	}
	
	private function getCacheKey($pkid) {
		return "CacheDAO_".get_class($this)."_$pkid";
	}
	
	private function getCacheHandle() {
		if ($this->cacheType == 'memcached') {
			return "HiCache_Memcached";
		} elseif ($this->cacheType == 'memcachedb') {
			return "HiCache_Memcachedb";
		} else {
			$this->throwException(9901, 'Wrong CacheHandle');
		}
	}
	
	private function getCache($pkid) {
		return json_decode(Hi::cache($this->getCacheKey($pkid), null, $this->getCacheHandle()), true);
	}
	
	private function setCache($pkid, $data = null) {
		Hi::writeCache($this->getCacheKey($pkid), json_encode($data), null, $this->getCacheHandle());
	}
	
	private function setRowsCache($rows) {
		if (is_array($rows)) {
			foreach ($rows as $v) {
				$this->setCache($v[$this->_primarykey], $v);
			}
		}
	}
	
	private function delRowsCache($rows) {
		if (is_array($rows)) {
			foreach ($rows as $v) {
				$this->setCache($v[$this->_primarykey]);
			}
		}
	}
	
	public function inserttable($insertdata, $returninsertid = false, $replace = false) {
		if (!$insertdata[$this->_primarykey]) {
			$pkid = parent::inserttable($insertdata, true, $replace);
			if ($returninsertid) $res = $pkid;
		} else {
			$pkid = $insertdata[$this->_primarykey];
			$res = parent::inserttable($insertdata, $returninsertid, $replace);
		}
		$this->setCache($pkid, $insertdata);
		return $res;
	}
	
	public function updatetable($updatedate, $where = array(), $addtype = false) {
		$res = parent::updatetable($updatedate, $where, $addtype);
		$rows = parent::getAll($where);
		$this->setRowsCache($rows);
		return $res;
	}
	
	public function executeSQL($sql, $byfield_ids = null) {
		$res = parent::executeSQL($sql, $byfield_ids);
		preg_match('/(from|into|update|replace)\s+([^\s]+)\s+.*(where.+?)$/is', $sql, $r);
		$selectsql = 'select * from `'.trim($r[2], '` ').'` '.$r[3];
		$rows = parent::findBySQL($selectsql, $byfield_ids);
		$this->setRowsCache($rows);
		return $res;
	}
	
	public function findBySQL($sql, $byfield_ids = null, $select = '') {
		$res = parent::findBySQL($sql, $byfield_ids, $select);
		preg_match('/(from|into|update|replace)\s+([^\s]+)\s+.*(where.+?)$/is', $sql, $r);
		$selectsql = 'select * from `'.trim($r[2], '` ').'` '.$r[3];
		$rows = parent::findBySQL($selectsql, $byfield_ids, $select);
		$this->setRowsCache($rows);
		return $res;
	}
	
	public function getByPKID($id, $pk = null, $byfield_id = null) {
		if (!$pk) $pk = $this->_primarykey;
		if ($pk != $this->_primarykey) return parent::getByPKID($id, $pk, $byfield_id);
		$data = $this->getCache($id);
		if ($data) {
			return $data;
		} else {
			$data = parent::getByPKID($id, $pk, $byfield_id);
			if ($data) $this->setCache($id, $data);
			return $data;
		}
	}
	
	public function delByPKID($id, $pk = null, $byfield_id = null) {
		if (!$pk) $pk = $this->_primarykey;
		$res = parent::delByPKID($id, $pk, $byfield_id);
		$this->setCache($id);
		return $res;
	}
	
	public function delAll($filters) {
		$rows = $this->getAll($filters);
		$res = parent::delAll($filters);
		$this->delRowsCache($rows);
		return $res;
	}
	
	public function truncate() {
		$rows = $this->getAll();
		$res = parent::truncate();
		$this->delRowsCache($rows);
		return $res;
	}
	
	protected function throwException($eid = 9854, $emsg = null) {
		if (is_null($emsg)) $emsg = "CacheDAO cann't support this method";
		throw new Exception($emsg, $eid);
	}
	
	
}