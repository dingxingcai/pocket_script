<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected  $connection = 'sqlsrv';

    protected  $table = 'employee';

    public $timestamps = false;
    //
}
