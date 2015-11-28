<?php

namespace Pvol\FlowMatrix\Protocol;

abstract class Hook
{
    use Pvol\FlowMatrix\Traits\Factory;
    
    public abstract function action($step, $status);
  
}
