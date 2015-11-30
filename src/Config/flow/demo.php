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
