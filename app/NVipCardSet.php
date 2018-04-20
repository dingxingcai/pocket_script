<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NVipCardSet extends Model
{
    protected $attributes = 'sqlsrv';

    public $table  = 'nVipCardSet';

    public $timestamps = false;
}
