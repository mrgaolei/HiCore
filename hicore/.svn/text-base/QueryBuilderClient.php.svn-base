<?php

/**
 * Description of QueryBuilder
 * 参考实现如下
 *
  TestController->dodefault() 实现
  
  public function dodefault() {
  
	  $qb = new QueryBuilderClient(&$this, 20);
	  $result= M('Test')->findUsersByServer($qb->toserver());        
	  $user =$result["data"];
	  $qb->fromserver($result["qb"]);
	      
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
        
        $querybuilder=new QueryBuilderServer($qbdata);
                
        $querybuilder->addintcond(array('uid'), 'u.');
        $querybuilder->addlikecond(array('email'), 'u.');
        $querybuilder->addstrcond(array('username'), 'u.');
        $querybuilder->addrangecond(array(array('intval', 'credit')), 'u.');
        $querybuilder->addorderfilter(array('dateline', 'updatetime'), 'u.');         
        
        $querybuilder->SELECT("u.*");
        $querybuilder->FROM("`{$this->_tablename}` u ");

        $querybuilder->excute();
        
        $result=array();
        $result["qb"]=$querybuilder->gettoclient();
        $result["data"]=$list;
        
        if ($querybuilder->getcount()) {
            $query = $this->db->query($querybuilder->getquerysql());
            while ($row = $this->db->fetch_array($query)) {             
                $row[flag_src]=($row['flag']==1)?"/images/yes.gif":"/images/no.gif";
                $list[] = $row;
            }
            $result["data"]= $list;
        }
        return $result;
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
class QueryBuilderClient {

    
    private $rowcount = 0;
    private $curpage=1;
    private $querysql = "";
    private $htmlvalue = array();
    private $condselect = array();
 //返回当前的出现的条件及值
    protected $urlargs = array();
    private $perpage = 10;
    
    private $customCA = false;
    private $customurl;

    public function __construct(BaseController &$controller, $perpage=20) {
        $this->controller = $controller;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->get = $controller->post;
        } else {
            $this->get = $controller->get;
        }
        $this->perpage = $perpage;

    }
    
    public function toserver(){    	
    	
    	$toserverdata=array(
     		  "perpage" =>$this->perpage,
    		  "get" => $this->get, 
       		);    	
       	return json_encode($toserverdata);
    	
    }
    public function fromserver($server_data){
    	
    	$serverdata=json_decode($server_data,TRUE);
    	
    	if(array_key_exists("rowcount",$serverdata)){
    		$this->rowcount=$serverdata["rowcount"];    		
    	}else{    		
    		$error = 'server data not contain rowcount';
    		throw new Exception($error);
    	}
    	
    	if(array_key_exists("perpage",$serverdata)){
    		$this->perpage=$serverdata["perpage"];   
    		$this->htmlvalue['perpages'] = array($this->perpage => ' selected'); 		
    	}else{    		
    		$error = 'server data not contain perpage';
    		throw new Exception($error);
    	}
    	
    	if(array_key_exists("curpage",$serverdata)){
    		$this->curpage=$serverdata["curpage"];    		
    	}else{    		
    		$error = 'server data not contain curpage';
    		throw new Exception($error);
    	}
    	
    	if(array_key_exists("urlargs",$serverdata)){
    		$this->urlargs=array_merge($this->urlargs, $serverdata["urlargs"]);  		
    	}else{    		
    		$error = 'server data not contain urlargs';
    		throw new Exception($error);
    	}
    	
    	if(array_key_exists("condselect",$serverdata)){
    		$this->condselect=$serverdata["condselect"];    
    	}
    	
    	if(array_key_exists("querysql",$serverdata)){
    		$this->querysql=$serverdata["querysql"];    		
    	}else{    		
    		$error = 'server data not contain querysql';
    		throw new Exception($error);
    	}
    	
    	if(array_key_exists("condvalue",$serverdata)){
    		$this->htmlvalue["condvalue"]=$serverdata["condvalue"];    
    	}
    	
    	if(array_key_exists("orderby",$serverdata)){
    		  $this->htmlvalue['orderby'] = array($serverdata["orderby"] => ' selected');    		
    	}
    	
    	if(array_key_exists("ordersc",$serverdata)){
    		  $this->htmlvalue['ordersc'] = array($serverdata["ordersc"] => ' selected');    		
    	}
    	
    	$multi = "";
        if ($this->rowcount) {
       	    $multi = $this->multi($this->rowcount, $this->perpage, $this->curpage);
        }
      	$this->htmlvalue['multi']=$multi;
      	
      	$this->htmlvalue['total'] = $this->rowcount;
      	$this->htmlvalue['curpage'] = $this->curpage;
      	$this->htmlvalue['perpage'] = $this->perpage;
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
        $multipage = $curpage > 1 ? '<a href="' . $this->geturl(array("page" => 1)) . '" class="first">首页</a>' : '';
        //上一页
        $multipage.= ( $curpage > 1 ? '<a href="' . $this->geturl(array("page" => ($curpage - 1))) . '" class="prev">上一页</a>' : '');
        //页面列表
        for ($i = $from; $i <= $to; $i++) {
            $multipage .= $i == $curpage ? '<strong>' . $i . '</strong>' :
                    '<a href="' . $this->geturl(array("page" => $i)) . '">' . $i . '</a>';
        }
        //下一页
        $multipage .= ( $curpage < $pages ? '<a href="' . $this->geturl(array("page" => ($curpage + 1))) . '" class="next">下一页</a>' : '');
        //尾页
        //$multipage .= ( 0 && $to < $pages ? '<a href="' . $this->geturl(array("page" => $pages)) . '" class="last">... ' . $realpages . '</a>  ' : '');
        $multipage .= ($curpage < $pages ? '<a href="' . $this->geturl(array("page" => $pages)) . '" class="last">尾页 </a>' : '');


        // $multipage = $multipage ? '<div class="pages">' . '<em>&nbsp;' . $num . '&nbsp;</em>' . $multipage . '</div>' : '';

        $multipage = $multipage ? '<div id="turn-page">' . '<span id="page-info">总计  <span id="totalRecords">' . $num . '</span>
                                个记录,第 <span id="pageCurrent">' . $curpage . '</span>
                                页/共 <span id="totalPages">' . $pages . '</span>
                                页  </span><span id="page-link">' . $multipage . ' </span></div>' : '';

        return $multipage;
    }
    
    public function seturl($baseurl) {
    	$this->customCA = true;
    	$this->customurl = urldecode($baseurl);
    }

    public function geturl($newpara) {
		unset($this->urlargs['controller']);
		unset($this->urlargs['action']);
		if ($this->customCA) {
			$url = $this->customurl;
			foreach ($newpara as $k => $v) {
				$url .= "&$k=$v";
			}
			return $url;
		} else {
			return util::makeurl($this->controller->str_controller, $this->controller->str_action, array_merge($this->urlargs, $newpara));
		}
    }

    public function addurlargs(array $urlargs) {
        $this->urlargs = array_merge($this->urlargs, $urlargs);
    }

    public function getcondselect() {
        return $this->condselect;
    }

}
?>
