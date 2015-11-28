<?php

namespace Pvol\Flow;

use Config;

class Flow{

    // 当前流程的配置文件
    public $config;
    // 当前流程的流程id
    public $flow_id;
    // 模板名称
    public $tpl_name;
    // 当前执行的角色
    public $running_role;
    // 当前执行的步骤
    public $running_step;
    
    public function __construct(array $attributes = array()) {
        $this->init($attributes);
        parent::__construct($attributes);
    }
    
    /**
     * @params params array 参数集合
     * array(project_name,flow_id,running_role,running_step)
     */
    public function init($params = array()) {
        
        if (isset($params['project_name'])) {
            $this->tpl_name = $params['project_name'];
            $this->config = Config::get("flow." . $this->tpl_name);
        }
        if (isset($params['flow_id'])) {
            $this->flow_id = $params['flow_id'];
        }
        if (isset($params['running_role'])) {
            $this->running_role = $params['running_role'];
        }
        if (isset($params['running_step'])) {
            $this->running_step = $params['running_step'];
        }
    }

    /**
     * 执行配置中的动作
     * @param $step 步骤
     * @param $action_index 顺序
     */
    public function run($step, $action_index){
        $this->running_step = $step;
        $action_config = $this->config['steps'][$step]['action'][$action_index];
        $action_arr = explode("@", $action_config);
        return $action_arr[0]::factory($this)->{$action_arr[1]}();
    }
    
}
