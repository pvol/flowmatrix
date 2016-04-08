<?php

namespace Pvol\FlowMatrix\Plugin\Delay;

use Pvol\FlowMatrix\Model;
use Pvol\FlowMatrix\Util;
use Pvol\FlowMatrix\Flow;
use DB,Config,Exception;

/**
 * 操作延时相关类
 */
class Delay {
    
    /**
     * @param flow 需要预设值tpl_name flow_id runing_role
     */
    public function __construct(Flow $flow) {
        $this->flow = $flow;
    }

    /**
     * 是不是延时者
     */
    public function isDelayMan($user) {
        $flow = Model\Flow::find($this->flow->flow_id);
        if(empty($flow)){
            throw new Exception("流程id不存在");
        }
        $flow_attr = $flow->getAttributes();
        $accepted_users = explode(",", $flow_attr['accepted_users']);
        // 判断用户是否是当前流程的接收人
        if(in_array($user->name, $accepted_users)){
            return true;     
        }
        // 判断用户有没有要执行的角色
        $current_roles = $user->roleNames();
        if(!in_array($this->flow->running_role, $current_roles)){
            return false;
        }
        // 判断用户是否是当前流程的接收角色
        $accepted_roles = explode(",", $flow_attr['accepted_roles']);
        foreach ($current_roles as $role) {
            if (in_array($role, $accepted_roles)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 保存延时原因
     * @param reason 延时原因
     * @param last_step 上个步骤
     * @param next_processing_time 下次执行时间
     */
    public function saveReason($reason, $last_step, $next_processing_time) {
        
        // 保存位置
        $flow = Model\Flow::find($this->flow->flow_id);
        if(empty($flow)){
            throw new Exception("流程id不存在");
        }
        $flow_attr = $flow->getAttributes();
        $steps = Config::get('flow.' . $this->flow->tpl_name . '.steps');
        $runing_config = $steps[$flow_attr['current_step']];
        Model\Step::create(array(
            'project_name' => $this->flow->tpl_name,
            'flow_id' => $this->flow->flow_id,
            'title' => $runing_config['title'],
            'real_title' => $runing_config['title'],
            'content' => '',
            'real_content' => $reason,
            'step' => $flow_attr['current_step'],
            'status' => Util\Status::DELAY,
            'created_user' => $user->name,
            'created_role' => $flow->running_role,
        ));
        Model\Step::find($last_step)->update([
            'next_processing_time' => $next_processing_time,
        ]);
        return true;
    }

}
