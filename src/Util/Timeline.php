<?php

namespace Pvol\FlowMatrix\Util;

use DB;

class Timeline {

    // 时间轴数据
    public static function get($flow_id) {
        $response = array();
        $info = DB::table('flows')->whereId($flow_id)->get();
        if (!empty($info)) {
            $response['info'] = (array) $info[0];
            $list = DB::select('select * from flow_steps where flow_id=' . $flow_id . ' and deleted_at is null order by id desc');
            foreach($list as &$row){
                $row = (array)$row;
            }
            $response['list'] = $list;
        } else {
            return false;
        }
        return $response;
    }

}
