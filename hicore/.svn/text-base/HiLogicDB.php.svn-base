<?php

!defined('HICORE_PATH') && exit('Access Denied');

/**
 * Description of HiLogicDB
 *
 * @author mrgaolei
 */
class HiLogicDB {
//TODO slave负载系数（权重）
    
    private $nodexml;
    private $masters;
    private $slaves;

    private $pool_m;
    private $pool_s;
    
    private $last_mlink;

    public function __construct(SimpleXMLElement $nodexml) {
	$this->nodexml = $nodexml;
	
	foreach ($nodexml->masters->master as $master) {
	    $node = array(
		'dbhost' => (string)$master->dbhost,
		'dbport' => (string)$master->dbport,
		'dbuser' => (string)$master->dbuser,
		'dbpasswd' => (string)$master->dbpasswd,
		'charset' => (string)$master->charset,
		'dbname' => (string)$master->dbname,
	    );
	    $this->masters[] = $node;
	    unset ($node);
	}

	foreach ($nodexml->slaves->slave as $slave) {
	    $node = array(
		'dbhost' => (string)$slave->dbhost,
		'dbport' => (string)$slave->dbport,
		'dbuser' => (string)$slave->dbuser,
		'dbpasswd' => (string)$slave->dbpasswd,
		'charset' => (string)$slave->charset,
		'dbname' => (string)$slave->dbname,
	    );
	    $this->slaves[] = $node;
	    unset ($node);
	}
	
	if (count($this->slaves) == 0) {
		$this->slaves[] = $this->masters[0];
	}
    }

    public function fetch_by_sql($sql, $select = '') {
	$list = array();
	$query = $this->query($sql);
	while ($row = $this->fetch_array($query)) {
	    if ($select && $row[$select]) {
		$list[$row[$select]] = $row;
	    } else {
		$list[] = $row;
	    }
	}
	return $list;
    }

    function fetch_array($query, $result_type = MYSQL_ASSOC) {
        return (is_resource($query)) ? mysql_fetch_array($query, $result_type) : false;
    }
    
    public function query($sql, $type = '') {
	global $mquerynum;
	$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
	$mlink = null;
	if (preg_match("/^\\s*SELECT/i", $sql)) {
	    // 查询，读slave
	    $randid = rand(0, count($this->slaves) - 1);
	    if (!$this->pool_s[$randid]) {
		// 建立链接
		$this->pool_s[$randid] = $this->connect($this->slaves[$randid]);
	    }
	    $mlink = $this->pool_s[$randid];
	} else {
	    // 修改，读masters
	    $randid = rand(0, count($this->masters) - 1);
	    if (!$this->pool_m[$randid]) {
		// 建立链接
		$this->pool_m[$randid] = $this->connect($this->masters[$randid]);
	    }
	    $mlink = $this->pool_m[$randid];
	}
	if (!($query = $func($sql, $mlink)) && $type != 'SILENT') {
	    throw new Exception("MySQL Query Error: ".$this->error($mlink)."\r\nwith SQL:$sql", $this->errno($mlink));
	}
	$mquerynum++;
	$this->last_mlink = $mlink;
	return $query;
    }

    public function connect($nodexml) {
	if (!$mlink = mysql_connect("{$nodexml['dbhost']}:{$nodexml['dbport']}", $nodexml['dbuser'], $nodexml['dbpasswd'], false)) {
	    throw new Exception('Can not connect to MySQL>>'.mysql_errno().':'.mysql_error());
	}
	if ($this->version($mlink) > '4.1') {
	    if ('utf-8' == strtolower($nodexml['charset'])) {
		$nodexml['charset'] = 'utf8'; 
	    }
	    if ($nodexml['charset']) {
		mysql_query("SET character_set_connection={$nodexml['charset']}, character_set_results={$nodexml['charset']}, character_set_client=binary", $mlink);
	    }
	    if ($this->version($mlink) > '5.0.1') {
		mysql_query("SET sql_mode=''", $mlink);
	    }
	}
	if ($nodexml["dbname"]) {
	    $this->select_db($nodexml["dbname"], $mlink);
	}
	Hi::addSharddb_mlink($mlink); //记录数据库链接，以备关闭
	return $mlink;
    }

    function version($mlink) {
	return mysql_get_server_info($mlink);
    }

    function select_db($dbname, $mlink) {
	return mysql_select_db($dbname, $mlink);
    }

    function fetch_first($sql) {
	$query = $this->query($sql);
	return $this->fetch_array($query);
    }
    
    function affected_rows($mlink) {
    	if (is_null($mlink)) $mlink = $this->last_mlink;
        return mysql_affected_rows($mlink);
    }

    function error($mlink) {
        return mysql_error($mlink);
    }

    function errno($mlink) {
        return mysql_errno($mlink);
    }
    
    function result($query, $row) {
        $query = @mysql_result($query, $row);
        return $query;
    }
    
    function insert_id() {
    	return ($id = mysql_insert_id($this->last_mlink)) >= 0 ? $id : $this->result($this->query('SELECT last_insert_id()'), 0);
    }

}
