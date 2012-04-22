<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of QueryPaenl
 *
 * 用法
 *
        $qb=new QueryBuilder(&$this,1);
        $qb->addintcond(array('user_id'),'u.');
        $qb->addlikecond(array('email'),'s.');
        $qb->addstrcond(array('user_name'),'s.');
        $qb->addrangecond(array( array('intval','store_id')),'u.');
        $qb->addorderfilter(array('add_time','last_login'),'u.');

        $qp=new QueryPanel($qb,$col=2);
        $qp->addcond('user_id','用户ID',$row,$col,$colspan=1,$type='text',$length=10,$optionvalue=array('key1'=>'value1','key2'=>'value2'));
        $qp->addcond('user_name','用户名',$row,$col);
        $qp->addcond('store_id','商铺ID');
        $qp->addcond('email','Email包含');
        $qp->addorder(array('add_time'=>'添加时间','last_login'=>'登陆时间'));
        $qp->addperpage('10,20,30,500');
        $qphtml=$qp->gethtml();
  
        $user = M('AdminUser')->findUsers(&$qb);

       

        $this->view->assign('users', $user);
         $this->view->assign('qphtml', $qphtml);

        $this->view->assign('htmlvalue', $qb->gethtmlvalue());
 *
 * @author wangqiang
 */
class QueryPanel {
    //put your code here
}
?>
