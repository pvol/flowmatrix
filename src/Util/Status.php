<?php

namespace Pvol\FlowMatrix\Util;

class Status
{
    const NOTPUBLISH = '99'; // 没有发布前的保存
    const DISPATCH = '88'; // 分发
    const DELAY = '77'; // 延时
    const CREATE = '10';
    const ARRIVED = '1';
    const ACCEPT = '2';
    const BACK = '3';
    const NEXT = '4';
    const AGREE = '5';
    const REJECT = '6';
    const SUSPEND = '7';
    const ABANDON = '8';
    const OVER = '9';
    
    
    public static $map = array(
        self::CREATE => "流程发布", 
        self::ARRIVED => "流程到达", 
        self::ACCEPT => "流程接受", 
        self::BACK => "流程驳回",
        self::NEXT => "流程通过",
        self::AGREE => "流程通过",
        self::REJECT => "流程不通过",
        self::SUSPEND => "流程挂起",
        self::ABANDON => "步骤放弃",
        self::OVER => "当前步骤完成",
        self::DISPATCH => "流程分配",
        self::DELAY => "流程操作超时",
        self::NOTPUBLISH => "流程录入",
    );
    
    public static function map(){
        return self::$map;
    }

}
