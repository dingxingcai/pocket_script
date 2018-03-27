<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NVipCardSign extends Model
{
    //

    protected $connection = 'sqlsrv';

    public $table = 'nVipCardSign';

    public $timestamps = false;
}
