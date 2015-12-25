<?php

namespace Pvol\FlowMatrix\Util;

use Pvol\FlowMatrix\Model;
use Config,DB,Log,Exception;
use Illuminate\Support\Arr;

class Step
{
    /**
     * 获取历史执行人
     * 
     * @param project_name 项目名称
     * @param flow_id 流程id
     * @param role 角色
     *  
     */
    public static function getHistoryUser($project_name, $flow_id, $role) {
        $sql = "select created_user,created_role from flow_steps where "
                . "project_name='{$project_name}' "
                . "and flow_id='{$flow_id}' "
                . "and created_role='{$role}' "
                . "and deleted_at is null "
                        . "limit 1";
        $list = DB::select($sql);
        $row = (array)$list[0];
        
        return $row;
    }
    
    /** 
     * 流程接受
     * 
     * @param flow lib/flow类
     * 
     */
    public static function accept($flow){
        
        $user = User::info();

        $flow_mod = Model\Flow::find($flow->flow_id);
        if(empty($flow_mod)){
            throw new Exception("流程id不存在");
        }
        $flow_info = $flow_mod->getAttributes();
        $steps = Config::get('flow.' . $flow->tpl_name . '.steps');
        $runing_config = $steps[$flow->running_step];

        // 合并接受人
        $new_accepted_users = array();
        if(!empty($flow_info['accepted_users'])){
            $new_accepted_users = explode(",", $flow_info['accepted_users']);
        }
        $new_accepted_users[] = $user->name;
        $new_accepted_users = implode(',', $new_accepted_users);
        // 合并角色
        $new_accepted_roles = array();
        if(!empty($flow_info['accepted_roles'])){
            $new_accepted_roles = explode(",", $flow_info['accepted_roles']);
        }
        $new_accepted_roles[] = $flow->running_role;
        $new_accepted_roles = implode(',', $new_accepted_roles);
        $flow_mod->update(array(
            'current_status' => Status::ACCEPT,
            'accepted_users' => $new_accepted_users,
            'accepted_roles' => $new_accepted_roles
        ));
        $step = Model\Step::create(array(
            'project_name' => $flow->tpl_name,
            'flow_id' => $flow->flow_id,
            'title' => $runing_config['title'],
            'real_title' => $runing_config['title'],
            'content' => '',
            'real_content' => '',
            'step' => $flow->running_step,
            'status' => Status::ACCEPT,
            'created_user' => $user->name,
            'created_role' => $flow->running_role,
        ));
        // 添加hook
        self::addHooks("after_step", $flow, $step, $flow->running_step, $flow->running_step, Status::ACCEPT);
    }
    
    /** 
     * 流程分配
     * 
     * @param flow lib/flow类
     * 
     */
    public static function dispatch($flow, $accepted_user, $accepted_role) {
        
        $user = User::info();
        
        $steps = Config::get('flow.' . $flow->tpl_name . '.steps');
        $runing_config = $steps[$flow->running_step];
        
        Model\Flow::where('id', $flow->flow_id)->update(array(
            'current_status' => Status::ACCEPT,
            'accepted_users' => $accepted_user,
            'accepted_roles' => $accepted_role,
        ));
        $step = Model\Step::create(array(
            'project_name' => $flow->tpl_name,
            'flow_id' => $flow->flow_id,
            'title' => $runing_config['title'],
            'real_title' => $runing_config['title'],
            'content' => '已分配至' . $accepted_user,
            'real_content' => '已分配至' . $accepted_user,
            'step' => $flow->running_step,
            'status' => Status::DISPATCH,
            'created_user' => $user->name,
            'created_role' => $flow->running_role,
        ));
        // 添加hook
        self::addHooks("after_step", $flow, $step, $flow->running_step, $flow->running_step, Status::DISPATCH);
    }
    
    /** 
     * 流程流转 
     * 
     * @param project_name 项目名称
     * @param flow lib/flow类
     * @param from 当前步骤名称
     * @param to 跳转到的步骤名称
     * @param action_status 执行动作对应的状态
     * 
     */
    public static function turnTo($flow, $from, $to, $action_status) {
        
        $user = User::info();
        $steps = Config::get('flow.' . $flow->tpl_name . '.steps');
        $to_config = $steps[$to];
        $runing_config = $steps[$flow->running_step];
        $flow_mod = Model\Flow::find($flow->flow_id);
        if(empty($flow_mod)){
            throw new Exception("流程id不存在");
        }
        $flow_info = $flow_mod->getAttributes();
        
        // 如果当前流程执行方式为accept(先接受后执行)
        $history_user = Step::getHistoryUser($flow->tpl_name, $flow->flow_id, $to_config['roles'][0]);
        switch($to_config['run_type']){
            case 'accept-only': // 始终先接受后执行
                $to_status = Status::ARRIVED;
                $to_accepted_users = '';
                $to_accepted_roles = '';
                break;
            case 'history': // 从历史记录中找执行人
                $to_status = Status::ACCEPT;
                $to_accepted_users = $history_user['created_user'];
                $to_accepted_roles = $to_config['roles'][0];  // 单角色可以这样使用，多角色后续再行考虑，当前需求无此要求
                break;
            case 'accept': 
            default: // 如果有历史则使用历史，如果没有历史则先接受再执行
                if(!empty($history_user)){
                    $to_status = Status::ACCEPT;
                    $to_accepted_users = $history_user['created_user'];
                    $to_accepted_roles = $to_config['roles'][0];  // 单角色可以这样使用，多角色后续再行考虑，当前需求无此要求
                } else {
                    $to_status = Status::ARRIVED;
                    $to_accepted_users = '';
                    $to_accepted_roles = '';
                }
                break;
        }
        Log::info("accepted_users:" . $to_accepted_users . " accepted_roles:" . $to_accepted_roles);
        // 如果之前已经有执行人
        if(!empty($flow_info['accepted_users'])){
            $from_accepted_users = explode(",", $flow_info['accepted_users']);
            // 去除当前执行人
            $from_accepted_users_reverse = array_flip($from_accepted_users);
            unset($from_accepted_users_reverse[$user->name]);
            $from_accepted_users = array_flip($from_accepted_users_reverse);
            // 添加跳转到的步骤执行人
            if(!empty($to_accepted_users)){
                $from_accepted_users[] = $to_accepted_users;
            }
            $to_accepted_users = implode(',', $from_accepted_users);
        }
        
        // 如果之前已经有执行人
        if(!empty($flow_info['accepted_roles'])){
            $from_accepted_roles = explode(",", $flow_info['accepted_roles']);
            // 去除当前执行人
            $from_accepted_roles_reverse = array_flip($from_accepted_roles);
            unset($from_accepted_roles_reverse[$flow->running_role]);
            $from_accepted_roles = array_flip($from_accepted_roles_reverse);
            // 添加跳转到的步骤执行人
            if(!empty($to_accepted_roles)){
                $from_accepted_roles[] = $to_accepted_roles;
            }
            $to_accepted_roles = implode(',', $from_accepted_roles);
        }
        
        $content = Arr::get($flow->request, 'content');
        $real_content = Arr::get($flow->request, 'real_content');
        
        // 更新流程主表
        $flow_mod->update(array(
            'current_step' => $to,
            'current_status' => $to_status,
            'accepted_users' => $to_accepted_users,
            'accepted_roles' => $to_accepted_roles
        ));

        $data = Arr::get($flow->request, 'data');
        foreach($data as &$item){
            $item = urlencode($item);
        }
        $data_json = empty($data) ? '' : json_encode($data);
        $data_json = urldecode($data_json);
        
        // 新增步骤执行记录
        $step = Model\Step::create(array(
            'project_name' => $flow->tpl_name,
            'flow_id' => $flow->flow_id,
            'title' => $runing_config['title'],
            'real_title' => $runing_config['title'],
            'content' => $content,
            'real_content' => $real_content,
            'step' => $flow->running_step,
            'status' => $action_status,
            'data' => $data_json,
            'created_user' => $user->name,
            'created_role' => $flow->running_role,
        ));
        
        // 添加hook
        self::addHooks("after_step", $flow, $step, $from, $to, $action_status);
    }
    
    /** 
     * 流程关闭
     * 
     * @param project_name 项目名称
     * @param flow lib/flow类
     * 
     */
    public static function over($flow){
        
        $user = User::info();
        $flow_mod = Model\Flow::find($flow->flow_id);
        if(empty($flow_mod)){
            throw new Exception("流程id不存在");
        }
        $flow_info = $flow_mod->getAttributes();
        $steps = Config::get('flow.' . $flow->tpl_name . '.steps');
        $runing_config = $steps[$flow->running_step];

        // 如果之前已经有执行人
        if(!empty($flow_info['accepted_users'])){
            $accepted_users = explode(",", $flow_info['accepted_users']);
            // 去除当前执行人
            $accepted_users_reverse = array_flip($accepted_users);
            unset($accepted_users_reverse[$user->name]);
            $accepted_users = array_flip($accepted_users_reverse);
            $accepted_users = implode(',', $accepted_users);
        }
        
        // 如果之前已经有执行人
        if(!empty($flow_info['accepted_roles'])){
            $accepted_roles = explode(",", $flow_info['accepted_roles']);
            // 去除当前执行人
            $accepted_roles_reverse = array_flip($accepted_roles);
            unset($accepted_roles_reverse[$flow->running_role]);
            $accepted_roles = array_flip($accepted_roles_reverse);
            $accepted_roles = implode(',', $accepted_roles);
        }
        $content = Arr::get($flow->request, 'content');
        $real_content = Arr::get($flow->request, 'real_content');

        $flow_mod->update(array(
            'accepted_users' => $accepted_users,
            'accepted_roles' => $accepted_roles
        ));
        $step = Model\Step::create(array(
            'project_name' => $flow->tpl_name,
            'flow_id' => $flow->flow_id,
            'title' => $runing_config['title'],
            'real_title' => $runing_config['title'],
            'content' => $content,
            'real_content' => $real_content,
            'step' => $flow->running_step,
            'status' => Status::OVER,
            'created_user' => $user->name,
            'created_role' => $flow->running_role,
        ));
        
        // 添加hook
        self::addHooks("after_step", $flow, $step, $flow->running_step, $flow->running_step, Status::OVER);
    }
    
    public static function addHooks($position, $flow, $step, $from, $to, $action_status){
        $hooks = $flow->config['hooks'];
        if (isset($hooks[$position])) {
            foreach ($hooks[$position] as $hook) {
                if(is_subclass_of($hook, "Pvol\FlowMatrix\Protocol\Hook\AfterStep")){
                    try{
                        $hook::factory()->action($flow->flow_id, $step->id, $from, $to, $action_status);
                    }catch(Exception $e){
                        Log::info($e);
                    }
                }
            }
        }
    }

}
