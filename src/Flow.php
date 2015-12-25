<?php

namespace Pvol\FlowMatrix;

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
        if (isset($params['request'])) {
            $this->request = $params['request'];
        }
    }

    /**
     * 执行配置中的动作
     * @param $step 步骤
     * @param $action_index 顺序
     * @more 如果是数字，按照键值取动作；
     * @more 如果是字符串，按照方法名取第一个符合条件的动作；
     */
    public function run($step, $action_index){
        $this->running_step = $step;
        $allow_actions = $this->config['steps'][$step]['action'];
        if(is_numeric($action_index)){
            $action_config = $allow_actions[$action_index];
            $action_arr = explode("@", $action_config);
            $obj = new $action_arr[0]($this);
        } else {
            foreach($allow_actions as $allow_action){
                $action_arr = explode("@", $allow_action);
                if($action_index === $action_arr[1]){
                    $obj = new $action_arr[0]($this);
                    break;
                }
            }
        }
        return $obj->{$action_arr[1]}();
    }
    
}
