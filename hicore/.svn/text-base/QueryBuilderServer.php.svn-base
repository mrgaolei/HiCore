<?php

/*
 *
 *
 *
 * @author wangqiang 2011/3/30
 */

class QueryBuilderServer {

    //put your code here

    private $select;
    private $from;
    private $where;
    private $orderby;
    private $groupby;
    private $intcond = array();
    private $strcond = array();
    private $rangecond = array();
    private $likecond = array();
    private $orderfilter = array();
    private $wherepre = '';
    private $orderpre = '';
    private $controller;
    private $get = array();
    private $rowcount = 0;
    private $querysql = "";
    private $condselect = array();
    //返回当前的出现的条件及值
    private $urlargs = array();
    private $perpage = 10;
    private $toclient = array();
    private $Dao = null;
    private $byfieldid;



    public function __construct($client_data, ShardDAO $_dao, $_byfieldid= null, $wherepre='', $orderpre='') {

        $clientdata = json_decode($client_data, TRUE);
        if (array_key_exists("perpage", $clientdata)) {
            $this->perpage = $clientdata["perpage"];
        }
        if (array_key_exists("get", $clientdata)) {
            $this->get = $clientdata["get"];
        }

        //$error = 'Always throw this error';
        //throw new Exception($error);

        $this->wherepre = $wherepre;
        $this->orderpre = $orderpre;
        $this->Dao = $_dao;
        $this->byfieldid = $_byfieldid;
    }

    public function SELECT($select) {
        $this->select = $select;
    }

    // $qb->From($aDAO->getTable($aid)." a,".$bDAO->getTable($bid)." b" );
    public function FROM($from) {
        $this->from = $from;
    }

    public function WHERE($where) {
        $this->where = $where;
    }

    public function ORDERBY($orderby) {
        $this->orderby = $orderby;
    }

    public function GROUPBY($groupby) {
        $this->groupby = $groupby;
    }

    public function addintcond($intcond=array(), $wherepre='#') {
        $this->intcond[$wherepre] = $intcond;
    }

    public function addstrcond($strcond=array(), $wherepre='#') {
        $this->strcond[$wherepre] = $strcond;
    }

    public function addrangecond($rangecond=array(), $wherepre='#') {
        $this->rangecond[$wherepre] = $rangecond;
    }

    public function addlikecond($likecond=array(), $wherepre='#') {
        $this->likecond[$wherepre] = $likecond;
    }

    public function addorderfilter($orderfilter=array(), $orderpre='#') {
        $this->orderfilter[$orderpre] = $orderfilter;
    }

    public function setwherepre($wherepre=0) {

        $this->wherepre = $wherepre;
    }

    public function setorderpre($orderpre=0) {

        $this->orderpre = $orderpre;
    }

    public function getcount() {
        return $this->rowcount;
    }

    public function getquerysql() {
        return $this->querysql;
    }

    public function excute() {

        $results = $this->excutewheres();

        $wherearr = $results['wherearr'];
        $wheresql = empty($wherearr) ? '1' : implode(' AND ', $wherearr);

        $wheresql = empty($this->where) ? $wheresql : "({$this->where}) AND (" . $wheresql . ")";

        //$mpurl .= '&' . implode('&', $results['urls']);

        $orders = $this->excuteorders();

        $ordersql = $orders['sql'];

        //  if ($orders['urls'])
        //     $mpurl .= '&' . implode('&', $orders['urls']);

        $this->toclient['orderby'] = $this->get['orderby'];
        $this->toclient['ordersc'] = $this->get['ordersc'];
        //$this->htmlvalue['orderby'] = array($this->get['orderby'] => ' selected');
        //$this->htmlvalue['ordersc'] = array($this->get['ordersc'] => ' selected');
        //$orderby = array($this->get['orderby'] => ' selected');
        //$ordersc = array($this->get['ordersc'] => ' selected');


        $perpage = empty($this->get['perpage']) ? $this->perpage : intval($this->get['perpage']);
        //if(!in_array($perpage, array(20,50,100))) $perpage = 20;
        // $mpurl .= '&perpage=' . $perpage;
        $this->urlargs = $this->get;
        unset($this->urlargs['c']);
        unset($this->urlargs['a']);
        $this->urlargs['perpage'] = $perpage;
        // $perpages = array($perpage => ' selected');
        $this->toclient['perpage'] = $perpage;
        //$this->htmlvalue['perpages'] = array($perpage => ' selected');

        $page = empty($this->get['page']) ? 1 : intval($this->get['page']);
        if ($page < 1)
            $page = 1;
        $start = ($page - 1) * $perpage;
        //检查开始数
        //ckstart($start, $perpage);


        $countsql = "SELECT COUNT(*) as num FROM " . $this->from . "  WHERE $wheresql ";
        if (!empty($this->groupby) && empty($this->orderby))
            $countsql = "SELECT COUNT(*) as num FROM " . $this->from . "  WHERE $wheresql group by $this->groupby";

        // $countarr = $this->db->fetch_first($countsql);

        $countarr = $this->Dao->fetch_first($countsql, $this->byfieldid);
        $this->rowcount = $countarr[num];


        $this->querysql = "SELECT " . $this->select . " FROM " . $this->from . "  WHERE $wheresql $ordersql LIMIT $start,$perpage";
        if (!empty($this->groupby) && empty($this->orderby))
            $this->querysql = "SELECT " . $this->select . " FROM " . $this->from . "  WHERE $wheresql $ordersql group by $this->groupby LIMIT $start,$perpage";


        $this->toclient['rowcount'] = $this->rowcount;
        $this->toclient['curpage'] = $page;
        $this->toclient['querysql'] = $this->querysql;
        $this->toclient['urlargs'] = $this->urlargs;
        $this->toclient['condselect'] = $this->condselect;

        //   $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM " . tname('space') . " s WHERE $wheresql"), 0);
    }

    private function excutewheres() {

        $wherearr = array();
        //$urls = array();

        foreach ($this->intcond as $wherepre => $condvalue) {
            $wherepre = $wherepre == '#' ? ($this->wherepre ? $this->wherepre : '') : $wherepre;
            foreach ($condvalue as $var) {
                $value = isset($this->get[$var]) ? $this->stripsearchkey($this->get[$var]) : '';
                //$this->htmlvalue['condvalue'][$var] = $value;
                $this->toclient['condvalue'][$var] = $value;
                if (strlen($value)) {
                    $wherearr[] = "{$wherepre}{$var}='" . intval($value) . "'";
                    //$urls[] = "$var=$value";
                    $this->urlargs[$var] = $value;
                    $this->condselect[$var] = $value;
                }
            }
        }

        foreach ($this->strcond as $wherepre => $condvalue) {
            $wherepre = $wherepre == '#' ? ($this->wherepre ? $this->wherepre : '') : $wherepre;
            foreach ($condvalue as $var) {
                $value = isset($this->get[$var]) ? $this->stripsearchkey($this->get[$var]) : '';
                //$this->htmlvalue['condvalue'][$var] = $value;
                $this->toclient['condvalue'][$var] = $value;
                if (strlen($value)) {
                    $wherearr[] = "{$wherepre}{$var}='$value'";
                    //$urls[] = "$var=" . rawurlencode($value);
                    $this->urlargs[$var] = rawurlencode($value);
                    $this->condselect[$var] = $value;
                }
            }
        }


        foreach ($this->rangecond as $wherepre => $condvalue) {
            $wherepre = $wherepre == '#' ? ($this->wherepre ? $this->wherepre : '') : $wherepre;
            foreach ($condvalue as $vars) {
                $value1 = isset($this->get[$vars[1] . '1']) ? $vars[0]($this->get[$vars[1] . '1']) : '';
                $value2 = isset($this->get[$vars[1] . '2']) ? $vars[0]($this->get[$vars[1] . '2']) : '';
                if ($value1) {
                    $wherearr[] = "{$wherepre}{$vars[1]}>='$value1'";
                    // $urls[] = "{$vars[1]}1=" . rawurlencode($this->get[$vars[1] . '1']);
                    $this->urlargs[$vars[1] . "1"] = rawurlencode($this->get[$vars[1] . '1']);
                    //$this->htmlvalue['condvalue'][$vars[1] . '1'] = $this->get[$vars[1] . '1'];
                    $this->toclient['condvalue'][$vars[1] . '1'] = $this->get[$vars[1] . '1'];
                }
                if ($value2) {
                    $wherearr[] = "{$wherepre}{$vars[1]}<='$value2'";
                    //$urls[] = "{$vars[1]}2=" . rawurlencode($this->get[$vars[1] . '2']);
                    $this->urlargs[$vars[1] . "2"] = rawurlencode($this->get[$vars[1] . '2']);
                    //$this->htmlvalue['condvalue'][$vars[1] . '2'] = $this->get[$vars[1] . '2'];
                    $this->toclient['condvalue'][$vars[1] . '2'] = $this->get[$vars[1] . '2'];
                }
                if ($value1 || $value2) {
                    $this->condselect[$vars[1]] = $value1 . "," . $value2;
                }
            }
        }

        foreach ($this->likecond as $wherepre => $condvalue) {
            $wherepre = $wherepre == '#' ? ($this->wherepre ? $this->wherepre : '') : $wherepre;
            foreach ($condvalue as $var) {
                $value = isset($this->get[$var]) ? $this->stripsearchkey($this->get[$var]) : '';
                //$this->htmlvalue['condvalue'][$var] = $value;
                $this->toclient['condvalue'][$var] = $value;
                if (strlen($value) >= 1) {
                    $wherearr[] = "{$wherepre}{$var} LIKE BINARY '%$value%'";
                    //$urls[] = "$var=" . rawurlencode($value);
                    $this->urlargs[$var] = rawurlencode($value);
                    $this->condselect[$var] = $value;
                }
            }
        }

        return array('wherearr' => $wherearr);
    }

    private function excuteorders() {

        $orders = array('sql' => '', 'urls' => array());

        $inorderfilter = false;
        if (!empty($this->get['orderby'])) {
            foreach ($this->orderfilter as $orderpre => $ordervalue) {
                $orderpre = $orderpre == '#' ? ($this->$orderpre ? $this->$orderpre : '') : $orderpre;
                if (in_array($this->get['orderby'], $ordervalue)) {
                    $inorderfilter = true;
                    $orders['sql'] = " ORDER BY {$orderpre}{$this->get[orderby]} ";
                    //$orders['urls'][] = "orderby={$this->get[orderby]}";
                    $this->urlargs['orderby'] = $this->get[orderby];
                }
            }
        }

        if (!$inorderfilter) {
            $orders['sql'] = empty($this->orderby) ? '' : " ORDER BY {$this->orderby} ";
            return $orders;
        }

        if (!empty($this->get['ordersc']) && $this->get['ordersc'] == 'desc') {
            //$orders['urls'][] = 'ordersc=desc';
            $this->urlargs['ordersc'] = 'desc';
            $orders['sql'] .= ' DESC ';
        } else {
            //$orders['urls'][] = 'ordersc=asc';
            $this->urlargs['ordersc'] = 'asc';
        }
        return $orders;
    }

    //处理搜索关键字
    private function stripsearchkey($string) {
        $string = trim($string);
        $string = str_replace('*', '%', addcslashes($string, '%_'));
        $string = str_replace('_', '\_', $string); //TODO ?不让出现 _
        return $string;
    }

    public function getcondselect() {
        return $this->condselect;
    }

    public function gettoclient() {
        return json_encode($this->toclient);
    }

}
?>
