<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ptype extends Model
{
    //
    protected $connection = 'sqlsrv';

    public $table = 'ptype';

    public $timestamps = false;
}
