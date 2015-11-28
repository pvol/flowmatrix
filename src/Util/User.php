<?php

namespace Pvol\FlowMatrix\Util;

use Auth;

class User {
    
    public static function info(){
        return Auth::user();
    }
}