<?php

namespace Pvol\FlowMatrix;

use Config,Exception;
use Pvol\FlowMatrix\Model;
use Pvol\FlowMatrix\Protocol;
use Pvol\FlowMatrix\Util;

class Action extends Protocol\Action{

    /** 
     * 新建
     */
    public function create() {
        return $this->publish();
    }

    /** 
     * 接受
     */
    public function accept() {

        $flow_mod = Model\Flow::find($this->flow->flow_id);
        if(empty($flow_mod)){
            throw new Exception("流程id不存在");
        }
        $flow_info = $flow_mod->getAttributes();
        $role_steps = Util\Condition::getRunningStepsByRoles($this->flow->tpl_name, $flow_info['current_step'], array($this->flow->running_role));
        $this->flow->running_step = $role_steps[0]['step_index']; // 设置第一个可执行的步骤为当前步骤
        
        // 校验是否可以执行接受动作
        Util\Condition::checkAcceptCondition($this->flow);

        // 跳转到指定步骤
        Util\Step::accept($this->flow);  
    }
    
    /** 
     * 流程分配
     */
    public function dispatch($accepted_user, $accepted_role) {
        
        // 校验是否可以执行分配动作
        Util\Condition::checkDispatchCondition($this->flow, $accepted_user, $accepted_role);
        
        // 跳转到指定步骤
        Util\Step::dispatch($this->flow, $accepted_user, $accepted_role);  
        
    }

    /** 
     * 保存
     */
    public function storage() {
        $user = Util\User::info();
        if (empty($this->flow->flow_id)) {
            $flow = Model\Flow::create(array(
                        'project_name' => $this->flow->tpl_name,
                        'current_status' => Util\Status::NOTPUBLISH,
                        'accepted_users' => '',
                        'accepted_roles' => '',
                        'current_step' => 'apply',
                        'created_user' => $user->name,
            ));
            $this->flow->flow_id = $flow->id;
            $steps = Config::get('flow.' . $this->flow->tpl_name . '.steps');
            $current = current($steps);
            $current_key = key($steps);
            $step = Model\Step::create(array(
                'project_name' => $this->flow->tpl_name,
                'flow_id' => $this->flow->flow_id,
                'title' => $current['title'],
                'real_title' => $current['title'],
                'content' => '新录入',
                'real_content' => '新录入',
                'step' => $current_key,
                'status' => Util\Status::NOTPUBLISH,
                'created_user' => $user->name,
                'created_role' => $this->flow->running_role,
            ));
            Util\Step::addHooks("after_step", $this->flow, $step, ZYD_STEP_APPLY, ZYD_STEP_APPLY, Util\Status::NOTPUBLISH);
            return $flow;
        }
        return false;
    }
    
    /** 
     * 发布
     */
    public function publish() {
        $user = Util\User::info();
        
        // 如果没有保存过，需要先保存
        $flow = false;
        if(empty($this->flow->flow_id)){
            $flow = $this->storage();
        }
        $steps = Config::get('flow.' . $this->flow->tpl_name . '.steps');
        $current = current($steps);
        $current_key = key($steps);
        $next_key = $current['createto'];
        
        // 校验是否可以流转
        Util\Condition::checkFlowOwner(
                $this->flow
        );
        $now = date('Y-m-d H:i:s');
        $yzt_fileno = Config::get('yzt.config.file_num_start') . date("Ymd", strtotime($now)) . str_pad($this->flow->flow_id, 3, 0, STR_PAD_LEFT);
        Model\Flow::where('id', $this->flow->flow_id)->update(array(
                    'current_status' => Util\Status::ARRIVED,
                    'current_step' => $next_key,
                    'created_at' => $now,
                    'yzt_fileno' => $yzt_fileno,
        ));
        $step = Model\Step::create(array(
            'project_name' => $this->flow->tpl_name,
            'flow_id' => $this->flow->flow_id,
            'title' => $current['title'],
            'real_title' => $current['title'],
            'content' => '新申请',
            'real_content' => '新申请',
            'step' => $current_key,
            'status' => Util\Status::CREATE,
            'created_user' => $user->name,
            'created_role' => $this->flow->running_role,
        ));
        // 添加hook
        Util\Step::addHooks("after_step", $this->flow, $step, ZYD_STEP_APPLY, ZYD_STEP_APPLY, Util\Status::CREATE);
        return $flow;
    }

    /** 
     * 打回
     */
    public function back() {
        $this->turnTo('backto', Util\Status::BACK);
    }

    /** 
     * 通过
     */
    public function next() {
        $this->turnTo('nextto', Util\Status::NEXT);
    }

    /** 
     * 同意
     */
    public function agree() {
        $this->turnTo('agreeto', Util\Status::AGREE);
    }

    /** 
     * 拒绝
     */
    public function reject() {
        $this->turnTo('rejectto', Util\Status::REJECT);
    }

    /** 
     * 放弃
     */
    public function abandon() {
        // 暂时无此功能，预留
    }

    /** 
     * 终端
     */
    public function suspend() {
        // 暂时不需要做任何操作，仅保存业务数据即可
    }

    /**
     * 当前步骤完成，但是不影响其他
     */
    public function over() {
        
        // 校验是否可以流转
        Util\Condition::checkTransitionCondition(
                $this->flow
        );
        
        // 结束当前步骤
        Util\Step::over(
                $this->flow
        );
    }
    
    /**
     * 流转
     */
    private function turnTo($dest_action, $dest_status){
        
        $flow_id = $this->flow->flow_id;
        $flow = Model\Flow::find($flow_id);
        if(empty($flow)){
            throw new Exception("流程id不存在");
        }
        $flow_info = $flow->getAttributes();
        $from = $flow_info['current_step'];
        // 目标步骤获取优先级 页面手动设置>系统配置
        if(isset($this->flow->request['dest'])){ 
            $to = $this->flow->request['dest']['dest_step'];
            $dest_status = $this->flow->request['dest']['dest_status'];
        } else {
            $steps = Config::get('flow.' . $this->flow->tpl_name . '.steps');
            $current_config = $steps[$from];
            $to = $current_config[$dest_action];
        }
        // 校验是否可以流转
        Util\Condition::checkTransitionCondition(
                $this->flow
        );
        
        // 流转
        Util\Step::turnTo(
                $this->flow,
                $from, 
                $to, 
                $dest_status
        );
    }

}
