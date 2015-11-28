<?php

namespace Pvol\FlowMatrix\Plugin\Delay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigDelay extends Model {

    use SoftDeletes;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    
}