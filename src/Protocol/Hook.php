<?php

namespace Pvol\Flow\Protocol;

abstract class Hook
{
    use Pvol\Flow\Traits\Factory;
    
    public abstract function action($step, $status);
  
}
