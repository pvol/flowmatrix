<?php

namespace Pvol\FlowMatrix\Protocol;

use Pvol\FlowMatrix\Traits\Factory;
use Pvol\FlowMatrix\Flow;

abstract class Action
{
    use Factory;
    
    protected $flow;

    /**
     * @param flow 需要预设值tpl_name flow_id runing_role
     */
    public function __construct(Flow $flow) {
        $this->flow = $flow;
    }
}
