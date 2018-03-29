<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PosType extends Model
{
    protected $connection = 'mysql';

    public $table = 'posType';

    public $timestamps = false;
}
