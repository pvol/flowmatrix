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
    
    /**
     * 根据用户名获取userId列表(单个)
     * 
     * @params names 用户名 可以是数组或逗号分隔的字符串，支持uid与name的混合
     * 
     */
    public static function getUserIdByName($name){
        if($user_ids = self::getUserIdByNames($name)){
            return $user_ids[0];
        } else {
            return false;
        }
    }
    
    /**
     * 根据用户名获取userId列表
     * 
     * @params names 用户名 可以是数组或逗号分隔的字符串，支持uid与name的混合
     * 
     */
    public static function getUserIdByNames($names){
        $name_ary = [];
        $uids = [];
        // 如果参数是字符串，则按照逗号拆分
        if(is_string($names)){
            $names = explode(",", $names);
        }
        // 遍历每个元素处理
        if(is_array($names)){
            foreach($names as $name){
                if(is_numeric($name)){
                    $uids[] = $name;
                } else {
                    $name_ary[] = $name;
                }
            }
        }
        
        // 如果存在用户名称则查询id
        if(count($name_ary)){
            // 查询内部库
            $inner_uids = \DB::connection("mysql-user-inner")
                    ->table("users")
                    ->whereIn("name", $name_ary)
                    ->groupBy("name")->lists("id");
            // 查询外部库
            $outter_uids = \DB::connection("mysql-user-outter")
                    ->table("users")
                    ->whereIn("name", $name_ary)
                    ->groupBy("name")->lists("id");
            // 合并内外部库查询到的内容
            $new_uids = array_merge($inner_uids, $outter_uids);
        }
        // 合并传入的用户ID与查到的用户ID
        $uids = array_merge($uids, $new_uids);
        if(count($uids)){
            return $uids;
        } else {
            return false;
        }
    }
}