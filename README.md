# FlowMatrix

## 简介

基于laravel的树形多分支流程流转引擎. 

## 功能与特点

* 网状调度
    *  通过一个配置文件可以完成任意网状流程调度
    *  支持一个流程多条线执行
* 多角色支持
    *  支持一个用户多角色执行
* 多方式派单
    * 支持执行人主动接受、历史记录自动派单、派单员手动派单等多种执行人分发方式。
* 多方向跳转
    * 支持流程create、agree、reject、next、back等多种操作
    * 支持自定义动作
* 扩展
    * 支持添加hook，业务逻辑解耦处理 

## 目录结构说明

```
|____Config // 配置文件（示例）
|____Data // 数据表结构
|____Util // 功能代码
|____Protocol // 协议
|____Plugin // 插件
|____Traits
|____Model // 数据模型
|____Action.php // 动作
|____Flow.php // 主程序

```
    
## 使用方法

### 1、配置数据库
```
如：src/data/flows.sql
```
### 2、添加配置文件
```
// 在config/flow/目录下添加如下配置文件：
// 配置文件名：项目名称.php

<?php
return [
    'hooks' => [
        'after_step' => [ // 流程步骤执行完时执行
            'App\Models\Zyd\Flow\Hook\PreOrder' // 需要是hook类的子类
        ],
    ],
    'steps' => [
        'step1'=>[
            'title' => 'step1',
            'action' => [ // 执行的动作,系统默认动作在Action中提供，也可以自定义
                'Pvol\FlowMatrix\Action@create',   // 新建
                'Pvol\FlowMatrix\Action@storage',   // 保存
                'Pvol\FlowMatrix\Action@publish',   // 发布
            ],
            'roles' => [ // 什么角色可以执行
                'channel'
            ],
            'condition'=>[ // 流程执行到第几步可以执行
            ],
            'createto'=>'step2',
            'run_type'=>'',  // 执行方式 现支持： accept(先接受后执行) history(历史执行人) 
        ],
        'step2'=>[
            'title' => 'step2',
            'action' => [ // 执行的动作,系统默认动作在Action中提供，也可以自定义
                'Pvol\FlowMatrix\Action@accept', // 接受
                'Pvol\FlowMatrix\Action@over', // 完成
            ],
            'roles' => [ // 什么角色可以执行
                'front_control'
            ],
            'condition'=>[ // 流程执行到第几步可以执行
                'step2'
            ],
            'run_type'=>'accept',  // 执行方式 现支持： accept(先接受后执行) history(历史执行人)
        ]
    ]
];

```

### 3、常用功能示例
* 新建流程

```
// 创建一个流程实例
$flow = new Flow([
	'project_name' => '', // 项目名称、配置文件名（不包含后缀）
	'running_role' => '', // 以哪个角色执行
]);

/* 
* step为需要执行的步骤
* action_index为需要执行的步骤在配置文件中action的序列或方法名 
* 如文档配置文件中的step1中的storage方法需要传参 $step='step1' $action_index=0 
*/

$flow->run($step, $action_index) ；
```

* 流程流转

```
// 创建一个流程实例
$flow = new Flow([
	'project_name' => '', // 项目名称、配置文件名（不包含后缀）
	'running_role' => '', // 以哪个角色执行
	'flow_id'      => '', // 流程id
]);

/* 
* step为需要执行的步骤
* action_index为需要执行的步骤在配置文件中action的序列或方法名
* 如文档配置文件中的step1中的storage方法需要传参 $step='step1' $action_index=0 
*/

$flow->run($step, $action_index) ；
```


