<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ValidationRule
 *
 * @author wangqiang
 */
class ValidationRule {
    //put your code here
      public $rule;
      public $args;
      public $msg;

      //普通校验方法
      //$vr=ValidationRule('min', 3, '不能小于3');
      //
      //如果要添加一个 callback 方法作为验证规则 $obj是对象，'method_name'是对象的方法
      //$vr=ValidationRule(array($obj, 'method_name'), $args, 'error_message');
      
      function __construct($rule, $args=array() ,$msg=null){
          $this->rule=$rule;
          $this->args=$args==null?array():$args;
          $this->msg=$msg;
      }

              
}
?>
