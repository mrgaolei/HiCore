<?php

/**
 * Description of LogicDAO
 *
 * @author mrgaolei
 */
class LogicDAO extends BaseModel {

    protected $_logicdb;
    protected $_logictable;
    protected $_logictablecfg;
    protected $config;
    protected $dbnodepool;
    protected $tableusedbnode;

    public function __construct($appid = null) {
	$tablepre = isset($this->_tablepre) ? $this->_tablepre : DB_TABLEPRE;
	$this->_tablename = $tablepre . $this->_tablename;
	$this->loadxml();
    }

    private function loadxml() {
	$xmlpath = APP_ROOT . '/logicdb/' . $this->_logicdb . '.xml';
	if (!file_exists($xmlpath))
	    return false;
	$xmlcontent = file_get_contents($xmlpath);
	$this->config = $xmlobj = new SimpleXMLElement($xmlcontent);
	if ((string) $this->config['name'] != $this->_logicdb)
	    return false;
	// 读取所有logictable，寻找符合的记录

	foreach ($this->config->logictable as $logictable) {
	    if ((string) $logictable['name'] == $this->_logictable) {
		$this->_logictablecfg = $logictable;
		break;
	    }
	}
    }

    protected function getTable($keyid) {
	if (!$this->_logictablecfg || $this->_logictablecfg['datashard'] != 'true')
	    return false;
	if (!is_array($keyid))
	    $keyid = array($keyid);
	$hashTables = array();
	$keyidcount = count($keyid);
	switch ((string) $this->_logictablecfg['shardtype']) {
	    case 'hash':
		for ($i = 0; $i < $keyidcount; $i++) {
		    $md5 = md5($keyid[$i]);
		    $matches = array();
		    preg_match((string) $this->_logictablecfg->matchrule, $md5, &$matches);
		    $table_postfix = $matches[0];
		    //$table_postfix_hex = hexdec($table_postfix);
		    $hashTables[$this->_tablename . $table_postfix][] = $keyid[$i];
		    $hashTablepfs[]= $table_postfix;
		    unset($keyid[$i]);
		}
		foreach ($hashTablepfs as $hash) {
		    $table_postfix_hex = hexdec($hash);
		    foreach ($this->_logictablecfg->shardrules->rule as $rule) {
			if ((string)$rule['to'] == '*' || $table_postfix_hex >= hexdec($rule['from']) && $table_postfix_hex <= hexdec($rule['to'])) {
			    $this->tableusedbnode[$this->_tablename . $hash] = (string) $rule['db'];
			    break;
			}
		    }
		}
		break;
	    case 'order':
	    default:
		for ($i = 0; $i < $keyidcount; $i++) {
		    $table_postfix = 0;
		    foreach ($this->_logictablecfg->shardrules->rule as $rule) {
			if (!isset($keyid[$i]))
			    continue;
			$table_postfix = $table_postfix + 1;
			if ((string) $rule['to'] == '*' || ($keyid[$i] >= $rule['from'] && $keyid[$i] <= $rule['to'])) {
			    $hashTables[$this->_tablename . '_' . $table_postfix][] = $keyid[$i];
			    $this->tableusedbnode[$this->_tablename . '_' . $table_postfix] = (string) $rule['db'];
			    unset($keyid[$i]);
			}
		    }
		}
		break;
	}
	return $hashTables;
    }

    public function getByKey($id) {
	$hashTables = $this->getTable($id);

	$result = array();

	foreach ($hashTables as $tablename => $ids) {
	    $dbnodeName = $this->tableusedbnode[$tablename];
	    if (!$this->dbnodepool[$dbnodeName]) {
		$this->dbnodepool[$dbnodeName] = $this->openLogicDB($dbnodeName);
	    }
	    $sql = "SELECT * FROM `{$tablename}` WHERE `{$this->_logictablecfg->byfield}` IN (" . implode(',', $ids) . ")";

	    if (!is_a($this->dbnodepool[$dbnodeName], "HiLogicDB")) {
		continue;
	    }
	    $datapart = $this->dbnodepool[$dbnodeName]->fetch_by_sql($sql, (string) $this->_logictablecfg->byfield);
	    foreach ($datapart as $key => $value) {
		$result[$key] = $value;
	    }
	    unset($datapart);
	}
	return $result;
    }

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
        
	$tables = $this->getTable($insertdata[(string)$this->_logictablecfg->byfield]);
	foreach ($tables as $tablename => $ids) {
	    $sql = "$fun INTO `{$tablename}` ($key) VALUES ($value)";
	    $dbnodeName = $this->tableusedbnode[$tablename];
	    if (!$this->dbnodepool[$dbnodeName]) {
		$this->dbnodepool[$dbnodeName] = $this->openLogicDB($dbnodeName);
	    }
	    if (!is_a($this->dbnodepool[$dbnodeName], "HiLogicDB")) {
		continue;
	    }
	    $this->dbnodepool[$dbnodeName]->query($sql);
	}
	if ($returninsertid) {
	    return $insertdata[(string)$this->_logictablecfg->byfield];
	}
    }

    private function openLogicDB($dbnodeName) {
	$dbnodePath = APP_ROOT . '/dbnode/' . $dbnodeName . '.xml';
	if (!file_exists($dbnodePath))
	    return false;
	$content = file_get_contents($dbnodePath);
	$dbnodeobj = new SimpleXMLElement($content);
	return new HiLogicDB($dbnodeobj);
    }

}
