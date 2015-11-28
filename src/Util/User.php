<?php

namespace Pvol\Flow\Util;

use Auth;

class User {
    
    public static function info(){
        return Auth::user();
    }
}