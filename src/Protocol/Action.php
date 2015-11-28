<?php

namespace Pvol\FlowMatrix\Protocol;

abstract class Action
{
    use Pvol\FlowMatrix\Traits\Factory;
    
    protected $flow;

    /**
     * @param flow 需要预设值tpl_name flow_id runing_role
     */
    public function __construct(Flow $flow) {
        $this->flow = $flow;
    }
}
