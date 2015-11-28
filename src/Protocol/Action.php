<?php

namespace Pvol\Flow\Protocol;

abstract class Action
{
    use Pvol\Flow\Traits\Factory;
    
    protected $flow;

    /**
     * @param flow 需要预设值tpl_name flow_id runing_role
     */
    public function __construct(Flow $flow) {
        $this->flow = $flow;
    }
}
