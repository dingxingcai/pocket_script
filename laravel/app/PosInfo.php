<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PosInfo extends Model
{
    protected $connection = 'sqlsrv';

    public $table = 'posInfo';

    public $timestamps = false;
}
