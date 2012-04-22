<?php

/**
 * 基础DAO
 * 支持逻辑库，逻辑表
 * @author mrgaolei
 *
 */
class ShardDAO {

	protected $_logicdb;
	protected $_logictable;

	protected $_tablename;
	protected $_primarykey;
	protected $_tablepre;

	private $_datashard;
	private $_shardtype;
	private $_byfield;
	private $_matchrule;
	private $_shardrules;
	private $_defaultdb;
	private $_db;

	private $_dbnode_path = 'dbnode';

	protected $_tablenameUseDbnode;
	private $_dbpools;

	public function __construct() {
		$tablepre = isset($this->_tablepre) ? $this->_tablepre : DB_TABLEPRE;
		$this->_tablename = $tablepre . $this->_tablename;
		$this->loadxml();
	}

	protected function loadxml() {
		$xmlpath = SVC_ROOT . '/logicdb/' . $this->_logicdb . '.xml';
		if (!file_exists($xmlpath)) {
			throw new Exception("LogicDB config file not found at $xmlpath.", 6001);
		}
		$xmlcontent = file_get_contents($xmlpath);
		$xmlobj = new SimpleXMLElement($xmlcontent);
		if ((string) $xmlobj['name'] != $this->_logicdb) {
			throw new Exception("LogicDB config file name tag is not '{$this->_logicdb}'", 6002);
		}
		$this->_defaultdb = (string)$xmlobj['dbnode'];
		// 读取所有logictable，寻找符合的记录
		foreach ($xmlobj->logictable as $logictable) {
			if ((string) $logictable['name'] == $this->_logictable) {
				$this->_datashard = (string)$logictable['datashard'] == 'true' ? true : false;
				if ($this->_datashard) {
					$this->_shardtype = (string)$logictable['shardtype'];
					$this->_db = (string)$logictable['dbnode'];
					$this->_byfield = (string)$logictable->byfield;
					if ($this->_shardtype == "hash") {
						$this->_matchrule = (string)$logictable->matchrule;
					}
					foreach ($logictable->shardrules->rule as $rule) {
						$onerule = array(
							'from' => (string)$rule['from'],
							'to' => (string)$rule['to'],
							'db' => (string)$rule['dbnode'],
						);
						$this->_shardrules[] = $onerule;
					}
				} else {
					$this->_db = (string)$logictable['dbnode'];
				}
				break;
			}
		}

	}

	public function getTables() {
		$tables = array();
		if ($this->_datashard) {
			switch ($this->_shardtype) {
				case 'hash':
					$tablepostfix = null;
					foreach ($this->_shardrules as $rule) {
						$from = hexdec($rule['from']);
						$to = hexdec($rule['to']);
						for ($i = $from; $i <= $to; $i ++) {
							$tablepostfix = dechex($i);
							//if (strlen($tablepostfix) == 1) $tablepostfix = '0'.$tablepostfix;
							$tablename = $this->_tablename . $tablepostfix;
							$this->_tablenameUseDbnode[$tablename] = $rule['db'];
							$tables[] = $tablename;
						}
					}
					break;
				case 'assigncol':
					break;
				case 'order':
				default:
					$tablepostfix = 0;
					foreach ($this->_shardrules as $rule) {
						$tablepostfix = $tablepostfix + 1;
						$tablename = $this->_tablename . $tablepostfix;
						$this->_tablenameUseDbnode[$tablename] = $rule['db'];
						$tables[] = $tablename;
					}
					break;
			}
		} else {
			$tables[] = $this->_tablename;
		}
		return $tables;
	}
	
	public function getTable($id) {
		if ($this->_datashard) {
			switch ($this->_shardtype) {
				case 'hash':
					$md5 = md5($id);
					$matches = array();
					preg_match($this->_matchrule, $md5, $matches);
					$tablepostfix = $matches[0];
					$tablepostfix_hex = hexdec($tablepostfix);
					foreach ($this->_shardrules as $rule) {
						if ($rule['to'] == '*' || ($tablepostfix_hex >= hexdec($rule['from']) && $tablepostfix_hex <= hexdec($rule['to']))) {
							$tablename = $this->_tablename . $tablepostfix;
							$this->_tablenameUseDbnode[$tablename] = $rule['db'];
							return $tablename;
						}
					}
					break;
				case 'assigncol':
					$tablename = $this->_tablename . $id;
					$this->_tablenameUseDbnode[$tablename] = $this->_defaultdb;
					return $tablename;
					break;
				case 'order':
				default:
					$tablepostfix = 0;
					foreach ($this->_shardrules as $rule) {
						$tablepostfix = $tablepostfix + 1;
						if ($rule['to'] == '*' || ($id >= $rule['from'] && $id <= $rule['to'])) {
							$tablename = $this->_tablename . $tablepostfix;
							$this->_tablenameUseDbnode[$tablename] = $rule['db'];
							return $tablename;
						}
					}
					break;
			}
		} else {
			return $this->_tablename;
		}
	}

	/**
	 * 根据byfield得到多条记录
	 * 支持分表数据
	 * @param mixed $ids 必须是byfield分表字段
	 * @param mixed $pks 当分表类型为assigncol时，$pks为查询条件值(如msgindex表，$ids为$fids, $pks为$mids)
	 * @param string $keyCol 返回数据以此字段值为key
	 */
	public function multiGet($ids, $pks = null, $keyCol = null, $orderby = array()) {
		if (!is_array($ids)) $ids = explode(',', $ids);
		$hashTables = array();
		if ($this->_datashard) {
			foreach ($ids as $id) {
				$found = false;
				$tablename = $this->getTable($id, $pks);
				if ($id) $hashTables[$tablename][] = $id;
				unset($tablename);
			}
		} else {
			$hashTables[$this->_tablename] = $ids;
			$this->_tablenameUseDbnode[$this->_tablename] = $this->_db;
		}
		$result = array();
		foreach ($hashTables as $tablename => $ids) {
			if (!$this->_dbpools[$this->_tablenameUseDbnode[$tablename]]) {
				// 如果还没有打开这个dbnode的数据连接，则打开之
				$this->_dbpools[$this->_tablenameUseDbnode[$tablename]] = $this->openDbNode($this->_tablenameUseDbnode[$tablename]);
			}
			if (!$this->_datashard) {
				$this->_byfield = $this->_primarykey;
			}
			if ($this->_shardtype == 'assigncol') {
				if (count($pks) < 1) continue;
				elseif (count($pks) < 2) $sqlplus = " AND `{$this->_primarykey}` = '$pks[0]'";
				else $sqlplus = " AND `{$this->_primarykey}` IN (".implode(',', $pks).")";
			}
			if (count($ids) < 1) {
				continue;
			} elseif (count($ids) < 2) {
				if (!$ids[0]) continue;
				else $sqlwhere = " AND `{$this->_byfield}` = '$ids[0]'";
			} else {
				$sqlwhere = " AND `{$this->_byfield}` IN ('".implode("','", $ids)."')";
			}
			if (count($orderby)) {
				$sqlplus .= " ORDER BY ";
			}
			foreach ($orderby as $k => $v) {
				$sqlplus .= " $k $v ";
			}
			$sql = "SELECT * FROM `{$tablename}` WHERE 1 " . $sqlwhere . $sqlplus;
			$tempresult = $this->_dbpools[$this->_tablenameUseDbnode[$tablename]]->fetch_by_sql($sql);
			if ($keyCol) {
				foreach ($tempresult as $v) {
					$result[$v[$keyCol]] = $v;
				}
			} else {
				foreach ($tempresult as $k => $v) {
					$result[] = $v;
				}
			}
			unset($tempresult);
		}
		return $result;
	}

	/**
	 * 持久化一个array
	 * 用insert语句保存一个数组到数据库
	 * 不是datashard情况，可以返回insertid
	 * @param array $insertdata
	 * @param boolean $returninsertid
	 * @param boolean $replace
	 */
	public function inserttable($insertdata, $returninsertid = false, $replace = false) {
		$keys = $values = array();
		$key = $value = "";
		foreach ($insertdata as $k => $v) {
			$keys[] = '`' . $k . '`';
			$values[] = '\'' . mysql_escape_string($v) . '\'';
		}
		$key = implode(',', $keys);
		$value = implode(',', $values);
		if ($replace) {
			$fun = "REPLACE";
		} else {
			$fun = "INSERT";
		}
		if ($this->_datashard) {
			$tablename = $this->getTable($insertdata[$this->_byfield]);
			$dbnodeName = $this->_tablenameUseDbnode[$tablename];
		} else {
			$tablename = $this->_tablename;
			$dbnodeName = $this->_db;
		}
		$sql = "$fun INTO `{$tablename}` ($key) VALUES ($value)";

		try {
			$this->getDbByNodeName($dbnodeName)->query($sql);
		} catch (Exception $e) {
			if ($e->getCode() == 1146) {
				# 分表不存在，先得到建表语句
				$csql = $this->showCreateTable();
				$csql = str_replace("CREATE TABLE `{$this->_tablename}_`", "CREATE TABLE `$tablename`", $csql);
				$this->getDbByNodeName($dbnodeName)->query($csql);
				$this->getDbByNodeName($dbnodeName)->query($sql);
			} else {
				throw $e;
			}
		}
		if ($returninsertid) {
			if ($this->isDatashard()) {
				return $insertdata[$this->_byfield];
			} else {
				return $this->getDbByNodeName($dbnodeName)->insert_id();
			}
		}
	}

	public function updatetable($updatedate, $where = array(), $addtype = false) {
		//exit(var_dump($where));
		$sets = array();
		$set = "";
		foreach ($updatedate as $k => $v) {
			if ($addtype) {
				$oper = '`' . $k . '` + ';
			} else {
				$oper = '';
			}
			$sets[] = '`' . $k . '` = ' . $oper .' \'' . $v . '\'';
		}
		$set = implode(',', $sets);
		$wheres = array();
		foreach ($where as $k => $v) {
			$wheres[] = '`' . $k . '` = \'' . $v . '\'';
		}

		if ($this->_datashard) {
			if (!isset($where[$this->_byfield])) throw new Exception("This DAO is datashard, you need to give byfield_id in \$where.", 7001);
			$tablename = $this->getTable($where[$this->_byfield]);
			$dbnodeName = $this->_tablenameUseDbnode[$tablename];
		} else {
			$tablename = $this->_tablename;
			$dbnodeName = $this->_db;
		}
		$where = implode(' AND ', $wheres);
		$sql = "UPDATE `{$tablename}` SET $set WHERE $where";
		//echo $sql;
		$this->getDbByNodeName($dbnodeName)->query($sql);
		return $this->getDbByNodeName($dbnodeName)->affected_rows(null);
	}

	/**
	 * 执行UPDATE,INSERT,DELETE等无返的SQL语句
	 * 无法得到受影响的行数
	 * @param string $sql
	 * @param mixed $byfield_ids
	 * @throws Exception
	 */
	public function executeSQL($sql, $byfield_ids = null) {
		$tables = array();
		if ($this->_datashard) {
			if (is_null($byfield_ids)) {
				throw new Exception("This DAO is datashard, must give byfield_ids", 7001);
			}
			$byfields = array();
			if (!is_array($byfield_ids)) {
				$byfields[] = $byfield_ids;
				$byfield_ids = $byfields;
				unset($byfields);
			}
			foreach ($byfield_ids as $id) {
				$tables[] = $this->getTable($id);
			}
		} else {
			$tables[] = $this->_tablename;
		}
		$result = array();
		foreach ($tables as $table) {
			if ($this->_datashard) {
				$dbnodeName = $this->_tablenameUseDbnode[$table];
			} else {
				$dbnodeName = $this->_db;
			}

			$this->getDbByNodeName($dbnodeName)->query(str_replace('{?tablename}', $table, $sql));
		}
	}

	/**
	 * 根据sql语句进行查询
	 * sql语句中的表名用{?tablename}字符串代替
	 * 如果是datashard，则必须给出byfield_ids，否则抛出异常
	 * @param string $sql
	 * @param mixed $byfield_ids
	 * @throws Exception
	 */
	public function findBySQL($sql, $byfield_ids = null, $select = '') {
		$tables = array();
		if ($this->_datashard) {
			if (is_null($byfield_ids) && strstr($sql, '{?tablename}')) {
				throw new Exception("This DAO is datashard, must give byfield_ids", 7001);
			}
			$byfields = array();
			if (!is_array($byfield_ids)) {
				$byfields[] = $byfield_ids;
				$byfield_ids = $byfields;
				unset($byfields);
			}
			foreach ($byfield_ids as $id) {
				$tables[] = $this->getTable($id);
			}
		} else {
			$tables[] = $this->_tablename;
		}
		$tables = array_unique($tables);
		$result = array();
		foreach ($tables as $table) {
			if ($this->_datashard) {
				$dbnodeName = $this->_tablenameUseDbnode[$table];
			} else {
				$dbnodeName = $this->_db;
			}

			$tmpresult = $this->getDbByNodeName($dbnodeName)->fetch_by_sql(str_replace('{?tablename}', $table, $sql));
			foreach ($tmpresult as $t) {
				if ($select && $t[$select]) {
					$result[$t[$select]] = $t;
				} else {
					$result[] = $t;
				}
			}
			unset($tmpresult);
		}
		return $result;
	}

	public function fetch_first($sql, $byfield_id = null) {
		if ($this->_datashard) {
			if (is_null($byfield_id)) {
				throw new Exception("This DAO is datashard, must give byfield_id", 7002);
			}
			$tablename = $this->getTable($byfield_id);
			$dbnodeName = $this->_tablenameUseDbnode[$tablename];
		} else {
			$tablename = $this->_tablename;
			$dbnodeName = $this->_db;
		}
		$sql = str_replace('{?tablename}', $tablename, $sql);
		return $this->getDbByNodeName($dbnodeName)->fetch_first($sql);

	}

	public function fetch_total($table, $where='1') {
		return $this->fetch_first("SELECT COUNT(*) num FROM $table WHERE $where");
	}

	/**
	 * 跨越分表的查询
	 * 可以不给分表条件，直接从所有表查询
	 * limit数可能不精确
	 * @param array $filters
	 * @param int $limit
	 */
	public function getAllBeyondTable($filters = array(), $limit = 0) {
		if (!$this->isDatashard()) {
			//throw new Exception("This DAO is NOT datashard, function getAllBeyondTable is unavaliable.", 7008);
		}
		$where = " WHERE 1 ";
		if ($filters && is_array($filters)) {
			foreach ($filters as $k => $v) {
				if (is_string($v) || is_numeric($v)) {
					$where .= " AND `$k` = '$v'";
				} elseif (is_array($v)) {
					$where .= " AND `$k` IN ('" . implode(',', $v) . "')";
				}
			}
		}
		$limitsql = "";
		if ($limit) $limitsql = " LIMIT $limit";
		$tables = $this->getTables();
		$result = array();
		foreach ($tables as $table) {
			if ($this->isDatashard()) {
				$sql = "SELECT * FROM `{$table}` $where $limitsql";
				try {
					$temp = $this->getDbByNodeName($this->_tablenameUseDbnode[$table])->fetch_by_sql($sql);
				} catch (Exception $e) {
					if ($e->getCode() == 1146) {
						continue;
					}
				}
			} else {
				$sql = "SELECT * FROM `{$this->_tablename}` $where $limitsql";
				$temp = $this->getDbByNodeName($this->_db)->fetch_by_sql($sql);
			}
			$result = array_merge($result, $temp);
			unset($temp);
			if ($limit && count($result) >= $limit) break;
		}
		return $result;
	}
	
	public function getRow($filters = array(), $order = array()) {
		$r = $this->getAll($filters, 1, 0, $order);
		if (is_array($r)) {
			$r = array_values($r);
			return $r[0];
		}
	}

	public function getAll($filters = array(), $pagesize = 0, $offset = 0, $order = array(), $select = '') {
		if ($this->_datashard) {
			if (!isset($filters[$this->_byfield])) throw new Exception("This DAO is datashard, you need to give byfield_id in \$filters.", 7001);
			$tablename = $this->getTable($filters[$this->_byfield]);
			$dbnodeName = $this->_tablenameUseDbnode[$tablename];
		} else {
			$tablename = $this->_tablename;
			$dbnodeName = $this->_db;
		}

		$where = " WHERE 1 ";
		if ($filters && is_array($filters)) {
			foreach ($filters as $k => $v) {
				if (is_string($v) || is_numeric($v)) {
					$where .= " AND `$k` = '$v'";
				} elseif (is_array($v)) {
					$where .= " AND `$k` IN ('" . implode("','", $v) . "')";
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
				if (strtoupper($k) == 'RAND()' || strtoupper($v) == 'RAND()' || strtoupper($v) == 'RAND') $orderstr .= ' RAND(),';
				else $orderstr .= " `$k` $v,";
			}
		}
		$orderstr = trim($orderstr, ', ');
		$sql = "SELECT * FROM `$tablename` $where $orderstr $limit";

		try {
			$result = $this->getDbByNodeName($dbnodeName)->fetch_by_sql($sql, $select);
		} catch (Exception $e) {
			if ($e->getCode() == 1146) {
				# 分表不存在，先得到建表语句
				$csql = $this->showCreateTable();
				$csql = str_replace("CREATE TABLE `{$this->_tablename}_`", "CREATE TABLE `$tablename`", $csql);
				$this->getDbByNodeName($dbnodeName)->query($csql);
				$this->getDbByNodeName($dbnodeName)->query($sql);
			} else {
				throw $e;
			}
		}
		return $result;
	}
	
	public function getCount($filters = array()) {
		if ($this->_datashard) {
			if (!isset($filters[$this->_byfield])) throw new Exception("This DAO is datashard, you need to give byfield_id in \$filters.", 7001);
			$tablename = $this->getTable($filters[$this->_byfield]);
			$dbnodeName = $this->_tablenameUseDbnode[$tablename];
		} else {
			$tablename = $this->_tablename;
			$dbnodeName = $this->_db;
		}
		$where = " WHERE 1 ";
		if ($filters && is_array($filters)) {
			foreach ($filters as $k => $v) {
				if (is_string($v) || is_numeric($v)) {
					$where .= " AND `$k` = '$v'";
				} elseif (is_array($v)) {
					$where .= " AND `$k` IN ('" . implode("','", $v) . "')";
				}
			}
		}
		$sql = "SELECT count(*) as num FROM `$tablename` $where";
		$result = $this->getDbByNodeName($dbnodeName)->fetch_by_sql($sql, $select);
		if (is_array($result)) {
			$result = array_values($result);
			return $result[0]['num'];
		}
	}

	public function getByPKID($id, $pk = null, $byfield_id = null) {
		$pkfield = is_null($pk) ? $this->_primarykey : $pk;
		$sql = "SELECT * FROM `{?tablename}` WHERE `$pkfield` = '$id'";
		return $this->fetch_first($sql, $byfield_id);
	}

	public function delByPKID($id, $pk = null, $byfield_id = null) {
		$pkfield = is_null($pk) ? $this->_primarykey : $pk;
		$sql = "DELETE FROM `{?tablename}` WHERE `$pkfield` = '$id'";
		return $this->executeSQL($sql, $byfield_id);
	}

	public function delAll($filters) {
		$where = '';
		if ($filters && is_array($filters)) {
			foreach ($filters as $k => $v) {
				if (is_string($v) || is_numeric($v)) {
					$where .= " AND `$k` = '$v'";
				} elseif (is_array($v)) {
					$where .= " AND `$k` IN ('" . implode("','", $v) . "')";
				}
			}
		}
		if ($where) {
			$where = "WHERE 1 $where";
		} else {
			throw new Exception("You can't use delAll function without \$filters.", 6001);
		}

		$tables = array();
		if ($this->_datashard) {
			if (isset($filters[$this->_byfield])) {
				$tables[] = $this->getTable($filters[$this->_byfield]);
			} else {
				$tables = $this->getTables();
			}
		} else {
			$sql = "DELETE FROM `{$this->_tablename}` $where";
			return $this->executeSQL($sql);
		}

		foreach ($tables as $table) {
			$sql = "DELETE FROM `{$table}` $where";
			try {
				$temp = $this->getDbByNodeName($this->_tablenameUseDbnode[$table])->query($sql);
			} catch (Exception $e) {
				if ($e->getCode() == 1146) continue;
			}
		}
	}

	public function truncate() {
		if ($this->isDatashard()) {
			throw new Exception("This DAO is datashard, function truncate is unavaliable.", 7002);
		}
		$this->getDbByNodeName($this->_db)->query("TRUNCATE TABLE `{$this->_tablename}`");
	}

	/**
	 * 根据一个dbnodeName得到一个HiLogicDB
	 * 支持自动维护连接池
	 * @param string $dbnodeName
	 * @throws Exception
	 */
	protected function getDbByNodeName($dbnodeName) {
		if (!$this->_dbpools[$dbnodeName]) {
			$this->_dbpools[$dbnodeName] = $this->openDbNode($dbnodeName);
		}
		if (!is_a($this->_dbpools[$dbnodeName], "HiLogicDB")) {
			throw new Exception("dbpool $dbnodeName is not HiLogicDB", 6003);
		}
		return $this->_dbpools[$dbnodeName];
	}

	private function openDbNode($dbnodeName) {
		$dbnodePath = SVC_ROOT . "/{$this->_dbnode_path}/" . $dbnodeName . '.xml';
		if (!file_exists($dbnodePath))
		return false;
		$content = file_get_contents($dbnodePath);
		$dbnodeobj = new SimpleXMLElement($content);
		return new HiLogicDB($dbnodeobj);
	}

	/**
	 * 返回该DAO是否分表
	 * @return boolean
	 */
	public function isDatashard() {
		return $this->_datashard;
	}

	public function setDbnodePath($path) {
		$this->_dbnode_path = $path;
	}

	/**
	 * 返回一个Sequence数值
	 * 不同的DAO返回的不一样
	 */
	public function seq() {
		$db = $this->openDbNode("seq");
		$tablename = "seq_{$this->_logicdb}_{$this->_logictable}";
		try {
			@$db->query("REPLACE INTO `$tablename` (stub) VALUES ('a')");
		} catch (Exception $e) {

			if ($e->getCode() == 1146) {
				$db->query("CREATE TABLE `{$tablename}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stub` char(1) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `stub` (`stub`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
				$db->query("REPLACE INTO `$tablename` (stub) VALUES ('a')");
			}
		}
		return $db->insert_id();
	}
	
	public function showCreateTable() {
		$defaulttable = $this->_tablename . '_';
		$sql = "SHOW CREATE TABLE `$defaulttable`";
		$create = $this->getDbByNodeName($this->_db)->fetch_first($sql);
		$sql = $create['Create Table'];
		return $sql;
	}
}