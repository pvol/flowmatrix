<?php

namespace Pvol\FlowMatrix\Util;

use Auth,Session;

class User {
    
    public static function info(){
        // 取登录帐号
        $user = Auth::user();
        if (!empty($user)) {
            return $user;
        }
        // 指定用户
        $user = self::getUser();
        if (!empty($user)) {
            return $user;
        }
        return false;
    }
    
    /**
     * 设置用户(第三方开放接口使用)
     */
    public static function setUser($user) {
        return Session::set('flow_matrix_user', $user);
    }
    
    public static function getUser() {
        return Session::get('flow_matrix_user');
    }
}