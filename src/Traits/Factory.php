<?php

namespace Pvol\FlowMatrix\Traits;

trait Factory {
    
    protected static $instance;
    
    public static function factory($params){
        return new static($params);
    }
    
    public static function singleton($params){
        if(!is_object(self::$instance)){
            return self::factory($params);
        } else {
            return self::$instance;
        }
    }
}