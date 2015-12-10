<?php

namespace Pvol\FlowMatrix\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Base extends Model {

    use SoftDeletes;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    
}