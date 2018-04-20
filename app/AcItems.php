<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AcItems extends Model
{
    protected $connection = 'sqlsrv';

    public $table = 'Ac_Items';

    public $timestamps = false;
}
