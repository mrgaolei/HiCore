<?php

/**
 * Description of QueryBuilder
 * 参考实现如下
 *
  TestController->dodefault() 实现
  public function dodefault() {
  $qb=new QueryBuilder(&$this,1);
  $qb->addintcond(array('user_id'),'u.');
  $qb->addlikecond(array('email'),'s.');
  $qb->addstrcond(array('user_name'),'s.');
  $qb->addrangecond(array( array('intval','store_id')),'u.');
  $qb->addorderfilter(array('add_time','last_login'),'u.');
  $user = M('AdminUser')->findUsers(&$qb);
  $this->view->assign('users', $user);

  //如果Form用get方法，则必须传递 controller aciton 两个隐藏参数
  // $this->view->assign('controller', $this->str_controller);
  // $this->view->assign('aciton', $this->str_action);
  $this->view->assign('htmlvalue', $qb->gethtmlvalue());
  $this->view->display('test');
  }

  AdminUserModel->findUsers(QueryBuilder &$querybuilder)实现
  public function findUsers(QueryBuilder &$querybuilder) {
  $list = array();
  $querybuilder->SELECT("u.*,s.email,s.user_name, g.grouptitle");
  $querybuilder->FROM("`{$this->_tablename}` u INNER JOIN `" . DB_TABLEPRE . "admin_user_group` g ON u.groupid = g.groupid INNER JOIN `" . DB_TABLEPRE . "users` s ON s.user_id = u.user_id");

  $querybuilder->excute();
  if ($querybuilder->getcount()) {
  $query = $this->db->query($querybuilder->getquerysql());
  while ($row = $this->db->fetch_array($query)) {
  $list[] = $row;
  }
  return $list;
  }
  }

  模板里的相应实现

  <div class="form-div">
  <form action="{url 'test'}" method="post" enctype="multipart/form-data" name="searchForm">
  <table cellspacing="1" cellpadding="3" width="100%">
  <tr>
  <td><div align="right"><strong>用户ID：</strong></div></td>
  <td colspan="3"><input type="text" name="user_id" value="$htmlvalue[condvalue][user_id]" size="10"></td>
  </tr>
  <tr>
  <td><div align="right"><strong>用户名：</strong></div></td>
  <td colspan="3"><input type="text" name="user_name" value="$htmlvalue[condvalue][user_name]"></td>
  </tr>
  <tr>
  <td><div align="right"><strong>email 包含：</strong></div></td>
  <td colspan="3"><input type="text" name="email" value="$htmlvalue[condvalue][email]"></td>
  </tr>
  <tr>
  <td><div align="right"><strong>商铺编号：</strong></div></td>
  <td colspan="3"><input type="text" name="store_id1" value="$htmlvalue[condvalue][store_id1]" size="10"> - <input type="text" name="store_id2" value="$htmlvalue[condvalue][store_id2]" size="10"></td>
  </tr>
  <tr>
  <td><div align="right"><strong>结果排序：</strong></div></td>
  <td colspan="3">
  <select name="orderby">
  <option value="">默认排序</option>
  <option value="add_time"$htmlvalue['orderby'][add_time]>建立时间</option>
  <option value="last_login"$htmlvalue['orderby'][last_login]>登陆时间</option>
  </select>
  <select name="ordersc">
  <option value="desc"$htmlvalue['ordersc'][desc]>递减</option>
  <option value="asc"$htmlvalue['ordersc'][asc]>递增</option>
  </select>
  <select name="perpage">
  <option value="1"$htmlvalue['perpages'][1]>每页显示1个</option>
  <option value="20"$htmlvalue['perpages'][20]>每页显示20个</option>
  <option value="50"$htmlvalue['perpages'][50]>每页显示50个</option>
  <option value="100"$htmlvalue['perpages'][100]>每页显示100个</option>
  </select>
  </td>
  </tr>

  <tr>
  <td colspan="4"><div align="center">
  <!--如果Form用get方法，则必须传递 controller aciton 两个隐藏参数 <input name="controller" type="hidden"  value='$controller' />
  <input name="action" type="hidden"  value='$action' />-->
  <input name="query" type="submit" class="button" id="query" value=" 搜索 " />
  <input name="reset" type="reset" class='button' value=' 重置 ' />
  </div></td>
  </tr>
  </table>
  </form>
  </div>

  <form method="post" action="" name="listForm">
  <div class="list-div" id="listDiv">
  <table width="100%" cellspacing="1" cellpadding="2" id="list-table">
  <tr>
  <th><input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" />用户ID</th>
  <th><a href="javascript:listTable.sort('$url','good_name'); ">用户名</a>$sort_user_name</th>
  <th>用户组</th>
  <th>E-mail</th>
  <th>建立时间</th>
  <th>登陆时间</th>
  <th>操作</th>
  </tr>
  {loop $users $u}
  <tr align="center" class="0" id="{$u['user_id']}">
  <td width="5%" align="left" class="first-cell" >
  <input type="checkbox" name="checkboxes[]" value="32" />$u['user_id']
  </td>
  <td>$u['user_name']</td>
  <td>$u['grouptitle']($u['groupid'])</td>
  <td width="25%">$u['email']</td>
  <td width="15%">{eval echo date('Y-n-d H:i:s', $u['add_time']);}</td>
  <td width="15%">{eval echo date('Y-n-d H:i:s', $u['last_login']);}</td>
  <td width="10%" align="center">
  <a href="{url 'user/edit/uid/$u['uid']'}">编辑</a> |
  <a href="javascript:;" onclick="listTable.remove($u['user_id'], '您确认要删除这条记录吗?', '{mkurl 'user', 'remove', array('uid'=>$u['uid'])}')" title="移除">移除</a>
  </td>
  </tr>
  {/loop}
  </table>

  <table id="page-table" cellspacing="0">
  <tr>
  <td align="right" nowrap="true">
  $htmlvalue['multi']
  </td>
  </tr>
  </table>
  </div>
  </form>
 *
 *
 *
 * @author wangqiang 2010/8/4
 */
class QueryBuilder {

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
    private $db = null;
    private $rowcount = 0;
    private $querysql = "";
    private $htmlvalue = array();
    private $condselect = array();
 //返回当前的出现的条件及值
    private $urlargs = array();
    private $perpage = 10;

    public function __construct(BaseController &$controller, $perpage=20, HiDB &$db=null, $wherepre='', $orderpre='') {
        $this->controller = $controller;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->get = $controller->post;
        } else {
            $this->get = $controller->get;
        }
        $this->perpage = $perpage;

        $this->wherepre = $wherepre;
        $this->orderpre = $orderpre;
	if (Hi::ini("db_disable")) {
	    ;
	} else {
	    if ($db == null) {
		$this->db = Hi::getDb();
	    } else {
		$this->db = $db;
	    }
	}
    }

    public function setDb(HiDB $db) {
	$this->db = $db;
    }

    public function SELECT($select) {
        $this->select = $select;
    }

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

    /*

     */

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

    /*
     * $htmlvalue['condvalue']  对应查询字段值的数组
     * $htmlvalue['orderby']    对应排序字段选择值的数组
     * $htmlvalue['ordersc']    对应排序方式选择值的数组
     * $htmlvalue['perpages']   对应每页记录数选择值的数组
     * $htmlvalue['multi']      对应分页html
     */

    public function gethtmlvalue() {
        return $this->htmlvalue;
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


        $this->htmlvalue['orderby'] = array($this->get['orderby'] => ' selected');
        $this->htmlvalue['ordersc'] = array($this->get['ordersc'] => ' selected');
        //$orderby = array($this->get['orderby'] => ' selected');
        //$ordersc = array($this->get['ordersc'] => ' selected');


        $perpage = empty($this->get['perpage']) ? $this->perpage : intval($this->get['perpage']);
        //if(!in_array($perpage, array(20,50,100))) $perpage = 20;
        // $mpurl .= '&perpage=' . $perpage;
        $this->urlargs['perpage'] = $perpage;
        // $perpages = array($perpage => ' selected');
        $this->htmlvalue['perpages'] = array($perpage => ' selected');

        $page = empty($this->get['page']) ? 1 : intval($this->get['page']);
        if ($page < 1)
            $page = 1;
        $start = ($page - 1) * $perpage;
        //检查开始数
        //ckstart($start, $perpage);

        
        $countsql = "SELECT COUNT(*) as num FROM " . $this->from . "  WHERE $wheresql ";
        if(!empty($this->groupby) && empty($this->orderby))
                $countsql = "SELECT COUNT(*) as num FROM " . $this->from . "  WHERE $wheresql group by $this->groupby";

        $countarr = $this->db->fetch_first($countsql);
        $this->rowcount = $countarr[num];

        $multi = "";
        if ($this->rowcount) {
            $multi = $this->multi($this->rowcount, $perpage, $page);
        }
        $this->querysql = "SELECT " . $this->select . " FROM " . $this->from . "  WHERE $wheresql $ordersql LIMIT $start,$perpage";
        if(!empty ($this->groupby) && empty($this->orderby))
                $this->querysql="SELECT " . $this->select . " FROM " . $this->from . "  WHERE $wheresql $ordersql group by $this->groupby LIMIT $start,$perpage";

        $this->htmlvalue['multi'] = $multi;


        //   $count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM " . tname('space') . " s WHERE $wheresql"), 0);
    }

    private function excutewheres() {

        $wherearr = array();
        //$urls = array();

        foreach ($this->intcond as $wherepre => $condvalue) {
            $wherepre = $wherepre == '#' ? ($this->wherepre ? $this->wherepre : '') : $wherepre;
            foreach ($condvalue as $var) {
                $value = isset($this->get[$var]) ? $this->stripsearchkey($this->get[$var]) : '';
                $this->htmlvalue['condvalue'][$var] = $value;

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
                $this->htmlvalue['condvalue'][$var] = $value;

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
                    $this->htmlvalue['condvalue'][$vars[1] . '1'] = $this->get[$vars[1] . '1'];
                }
                if ($value2) {
                    $wherearr[] = "{$wherepre}{$vars[1]}<='$value2'";
                    //$urls[] = "{$vars[1]}2=" . rawurlencode($this->get[$vars[1] . '2']);
                    $this->urlargs[$vars[1] . "2"] = rawurlencode($this->get[$vars[1] . '2']);
                    $this->htmlvalue['condvalue'][$vars[1] . '2'] = $this->get[$vars[1] . '2'];
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
                $this->htmlvalue['condvalue'][$var] = $value;

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

    //分页
    private function multi($num, $perpage, $curpage, $page = 10) {
        //global $_SCONFIG;
        //$page = 5;
        $multipage = '';
        //$mpurl .= strpos($mpurl, '?') ? '&' : '?';
        $realpages = 1;
        if ($num > $perpage) {
            $offset = 2;
            $realpages = @ceil($num / $perpage);
            //$pages = $_SCONFIG['maxpage'] && $_SCONFIG['maxpage'] < $realpages ? $_SCONFIG['maxpage'] : $realpages;
            $pages = $realpages;
            if ($page > $pages) {
                $from = 1;
                $to = $pages;
            } else {
                $from = $curpage - $offset;
                $to = $from + $page - 1;
                if ($from < 1) {
                    $to = $curpage + 1 - $from;
                    $from = 1;
                    if ($to - $from < $page) {
                        $to = $page;
                    }
                } elseif ($to > $pages) {
                    $from = $pages - $page + 1;
                    $to = $pages;
                }
            }

            $multipage = $this->getmultihtml($num, $curpage, $pages, $from, $to);
        }
        //$maxpage = $realpages;
        return $multipage;
    }

    /*
     * 子类可以继承该方法以实现自己的分页模板
     */

    protected function getmultihtml($num, $curpage, $pages, $from, $to) {
        $multipage = '';
        //第一页
        //$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="' . $this->geturl(array("page" => 1)) . '" class="first">1 ...</a>  ' : '');
        $multipage = '<a href="' . $this->geturl(array("page" => 1)) . '" class="first">首页</a>  ';
        //上一页
        $multipage.= ( $curpage > 1 ? '<a href="' . $this->geturl(array("page" => ($curpage - 1))) . '" class="prev">上一页</a>  ' : '');
        //页面列表
        for ($i = $from; $i <= $to; $i++) {
            $multipage .= $i == $curpage ? '<strong>' . $i . '</strong>' :
                    '<a href="' . $this->geturl(array("page" => $i)) . '">' . $i . '</a>';
        }
        //下一页
        $multipage .= ( $curpage < $pages ? '<a href="' . $this->geturl(array("page" => ($curpage + 1))) . '" class="next">下一页</a>  ' : '');
        //尾页
        //$multipage .= ( 0 && $to < $pages ? '<a href="' . $this->geturl(array("page" => $pages)) . '" class="last">... ' . $realpages . '</a>  ' : '');
        $multipage .= '<a href="' . $this->geturl(array("page" => $pages)) . '" class="last">  尾页 </a>  ';


        // $multipage = $multipage ? '<div class="pages">' . '<em>&nbsp;' . $num . '&nbsp;</em>' . $multipage . '</div>' : '';

        $multipage = $multipage ? '<div id="turn-page">' . '总计  <span id="totalRecords">' . $num . '</span>
                                个记录,共 <span id="totalPages">' . $pages . '</span>
                                页/第 <span id="pageCurrent">' . $curpage . '</span>
                                页  <span id="page-link">' . $multipage . ' </span></div>' : '';

        return $multipage;
    }

    public function geturl($newpara) {

	return util::makeurl($this->controller->str_controller, $this->controller->str_action, array_merge($this->urlargs, $newpara));
    }

    public function addurlargs(array $urlargs) {
        $this->urlargs = array_merge($this->urlargs, $urlargs);
    }

    public function getcondselect() {
        return $this->condselect;
    }

}
?>
