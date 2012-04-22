<?php

!defined('HICORE_PATH') && exit('Access Denied');

class Hidb {

    var $mlink;

    function Hidb($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset='utf8', $pconnect=0, $usetrans = true, $autocommit = false, $new_link = false) {
        if ($pconnect) {
            if (!$this->mlink = @mysql_pconnect($dbhost, $dbuser, $dbpw, $new_link)) {
                $this->halt('Can not connect to MySQL');
            }
        } else {
            if (!$this->mlink = @mysql_connect($dbhost, $dbuser, $dbpw, $new_link)) {
                $this->halt('Can not connect to MySQL');
            }
        }
        if ($this->version() > '4.1') {
            if ('utf-8' == strtolower($dbcharset)) {
                $dbcharset = 'utf8';
            }
            if ($dbcharset) {
                mysql_query("SET character_set_connection=$dbcharset, character_set_results=$dbcharset, character_set_client=binary", $this->mlink);
            }
            if ($this->version() > '5.0.1') {
                mysql_query("SET sql_mode=''", $this->mlink);
            }
        }
        if ($dbname) {
            mysql_select_db($dbname, $this->mlink);
        }
        if ($usetrans) {
            $this->starttransaction();
            $this->autocommit($autocommit);
        }
    }

    function select($sql, $keyfield = '') {
        $array = array();
        $result = $this->query($sql);
        while ($r = $this->fetch_array($result)) {
            if ($keyfield) {
                $key = $r[$keyfield];
                $array[$key] = $r;
            } else {
                $array[] = $r;
            }
        }
        $this->free_result($result);
        return $array;
    }

    function select_db($dbname) {
        return mysql_select_db($dbname, $this->mlink);
    }

    function starttransaction() {
//            echo 'START TRANSACTION';
        $this->query("START TRANSACTION");
    }

    function commit() {
//            echo 'COMMIT';
        $this->query("COMMIT");
    }

    function rollback() {
//            echo 'ROLLBACK';
        $this->query("ROLLBACK");
    }

    function autocommit($auto = true) {
        $auto = $auto ? 1 : 0;
        $this->query("SET AUTOCOMMIT = $auto");
    }

    function fetch_array($query, $result_type = MYSQL_ASSOC) {
        return (is_resource($query)) ? mysql_fetch_array($query, $result_type) : false;
    }

    function result_first($sql) {
        $query = $this->query($sql);
        return $this->result($query, 0);
    }

    function fetch_first($sql) {
        $query = $this->query($sql);
        return $this->fetch_array($query);
    }

    function fetch_by_field($table, $field, $value, $select_fields='*') {
        $query = $this->query("SELECT $select_fields FROM " . DB_TABLEPRE . "$table WHERE $field='$value'");
        return $this->fetch_array($query);
    }

    function fetch_by_sql($sql, $select = '') {
        $list = array();
        $query = $this->query($sql);
        while ($row = $this->fetch_array($query)) {
             if($row[$select]) {                  //2010-12-20 edit fantom
                $list[$row[$select]] = $row;
            }else {
                $list[] = $row;
            }
        }
        return $list;
    }

    function update_field($table, $field, $value, $where) {
        return $this->query("UPDATE " . DB_TABLEPRE . "$table SET $field='$value' WHERE $where");
    }

    function fetch_total($table, $where='1') {
        return $this->result_first("SELECT COUNT(*) num FROM $table WHERE $where");
    }

    function query($sql, $type = '', $ignore = false) {
        global $mquerynum;
        $func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
        if (!($query = $func($sql, $this->mlink)) && $type != 'SILENT') {
            $ignore || $this->halt("MySQL Query Error", 'TRUE', $sql);
        }
        $mquerynum++;
        return $query;
    }

    function affected_rows() {
        return mysql_affected_rows($this->mlink);
    }

    function error() {
        return (($this->mlink) ? mysql_error($this->mlink) : mysql_error());
    }

    function errno() {
        return intval(($this->mlink) ? mysql_errno($this->mlink) : mysql_errno());
    }

    function result($query, $row) {
        $query = @mysql_result($query, $row);
        return $query;
    }

    function num_rows($query) {
        $query = mysql_num_rows($query);
        return $query;
    }

    function num_fields($query) {
        return mysql_num_fields($query);
    }

    function free_result($query) {
        return mysql_free_result($query);
    }

    function insert_id() {
        return ($id = mysql_insert_id($this->mlink)) >= 0 ? $id : $this->result($this->query('SELECT last_insert_id()'), 0);
    }

    function fetch_row($query) {
        $query = mysql_fetch_row($query);
        return $query;
    }

    function fetch_fields($query) {
        return mysql_fetch_field($query);
    }

    function version() {
        return mysql_get_server_info($this->mlink);
    }

    function close() {
        return mysql_close($this->mlink);
    }

    function halt($msg, $debug=true, $sql='') {
        @ini_set("date.timezone", "Asia/Shanghai");
        if ($debug) {
            $output .="<html>\n<head>\n";
            $output .="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . Hi::ini('response_charset') . "\">\n";
            $output .="<title>$msg</title>\n";
            $output .="</head>\n<body><table>";
            $output .="<b>HiCORE Error Info</b><table><tr><td width='100px'><b>Message</b></td><td>$msg</td></tr>\n";
            $output .="<tr><td><b>Time</b></td><td>" . date("Y-m-d H:i:s") . "<br /></td></tr>\n";
            $output .="<tr><td><b>Script</b></td><td> " . $_SERVER['PHP_SELF'] . "<br /></td></tr>\n\n";
            $output .="<tr><td><b>QueryString</b></td><td> " . $_SERVER['QUERY_STRING'] . "<br /></td></tr>\n\n";
            $output .="<tr><td><b>SQL</b></td><td> " . htmlspecialchars($sql) . "<br />\n</td></tr><tr><td><b>Error</b></td><td>  " . $this->error() . "</td></tr><br />\n";
            $output .="<tr><td><b>Errno.</b></td><td>  " . $this->errno() . "</td></tr></table>";

            $output .="\n</body></html>";
            echo $output;
            exit();
        }
        $this->errorlog($msg, $sql);
    }

    function errorlog($msg, $sql) {
        $error = "<?php exit;?>" . "\t" . time() . "\t" . util::getip() . "\tMysql\t" . $_SERVER['PHP_SELF'] . "\t" . $this->errno() . "\t" . $this->error() . "\t$sql\n";
        file::forcemkdir(HICMS_ROOT . "/data/logs");
        @$fp = fopen(HICMS_ROOT . "/data/logs/" . date('Ym') . "_errorlog.php", "a");
        @flock($fp, 2);
        @fwrite($fp, $error);
        @fclose($fp);
    }

    }

