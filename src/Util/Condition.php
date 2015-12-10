<?php

namespace Pvol\FlowMatrix\Util;

use Pvol\FlowMatrix\Model;
use Config,Exception;

class Condition {

    /**
     * 根据角色获取所有可执行的步骤
     * @return array(array(角色,步骤名))
     */
    public static function getAllStepsByRoles($project_name, $roles) {

        $step_configs = Config::get('flow.' . $project_name . '.steps');
        $role_steps = array();
        foreach ($roles as $role) {
            foreach ($step_configs as $step_index => $step_config) {
                if (in_array($role, $step_config['roles'])) {
                    $role_steps[] = array('role' => $role, 'step_index' => $step_index, 'condition'=>$step_config['condition']);
                }
            }
        }
        return $role_steps;
    }

    /**
     * 根据角色获取当前可执行的步骤
     * @return array(array(角色,步骤名))
     */
    public static function getRunningStepsByRoles($project_name, $current_step, $roles) {

        $role_steps = self::getAllStepsByRoles($project_name, $roles);
        $running_role_steps = array();
        foreach ($role_steps as $role_step) {
            if (in_array($current_step, $role_step['condition'])) {
                $running_role_steps[] = $role_step;
            }
        }
        return $running_role_steps;
    }
    
    /**
     * 校验当前执行人是否为流程创建人
     * 
     * @param flow lib/flow类
     * 
     */
    public static function checkFlowOwner($flow) {

        $flow_mod = Model\Flow::find($flow->flow_id);
        if(empty($flow_mod)){
            throw new Exception("流程id不存在");
        }
        $flow_info = $flow_mod->getAttributes();
        
        $user = User::info();

        if($user->name !== $flow_info['created_user']){
            throw new Exception("流程已接受！");
        }
        return true;
    }

    /**
     * 校验当前是否可以执行接受动作
     * 
     * @param flow lib/flow类
     * 
     */
    public static function checkAcceptCondition($flow) {

        $flow_mod = Model\Flow::find($flow->flow_id);
        if(empty($flow_mod)){
            throw new Exception("流程id不存在");
        }
        $flow_info = $flow_mod->getAttributes();
        
        $user = User::info();
        $accepted_users = explode(",", $flow_info['accepted_users']);
        if(in_array($user->name, $accepted_users)){
            throw new Exception("流程已接受！");
        }
        
        $current = $flow_info['current_step'];

        // 获取当前用户角色可以执行当前流程的哪些正在执行的步骤
        $role_steps = self::getRunningStepsByRoles($flow->tpl_name, $current, array($flow->running_role));
        if (empty($role_steps)) {
            throw new Exception("流程已修改！");
        }

        // 判断是否已被接收
        $accepted_roles = explode(",", $flow_info['accepted_roles']);
        if (in_array($flow->running_role, $accepted_roles)) {
            throw new Exception("流程已被他人接受！");
        }

        return true;
    }
    
    /**
     * 校验当前是否可以执行分配动作
     * 
     * @param flow lib/flow类
     * 
     */
    public static function checkDispatchCondition($flow, $accepted_user, $accepted_role) {

        $flow_mod = Model\Flow::find($flow->flow_id);
        if(empty($flow_mod)){
            throw new Exception("流程id不存在");
        }
        $flow_info = $flow_mod->getAttributes();
        
        $current = $flow_info['current_step'];

        // 获取当前用户角色可以执行当前流程的哪些正在执行的步骤
        $role_steps = self::getRunningStepsByRoles($flow->tpl_name, $current, array($flow->running_role));
        if (empty($role_steps)) {
            throw new Exception("流程已修改！");
        }
        // 获取被分配用户角色可以执行当前流程的哪些正在执行的步骤
        $role_steps = self::getRunningStepsByRoles($flow->tpl_name, $current, array($accepted_role));
        if (empty($role_steps)) {
            throw new Exception("当前用户无权分配指定的角色！");
        }
        return true;
    }

    /**
     * 校验当前是否可以执行打回、跳过、通过、不通过、完成等流转动作
     * 
     * @param flow lib/flow类
     * 
     */
    public static function checkTransitionCondition($flow) {
        $user = User::info();
        $flow_mod = Model\Flow::find($flow->flow_id);
        if(empty($flow_mod)){
            throw new Exception("流程id不存在");
        }
        $flow_info = $flow_mod->getAttributes();

        $accepted_users = explode(",", $flow_info['accepted_users']);
        if (!in_array($user->name, $accepted_users)) {
            throw new Exception("当前用户不在执行列表中！");
        }

        return true;
    }

}
