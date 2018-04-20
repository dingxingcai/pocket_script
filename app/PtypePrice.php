<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PtypePrice extends Model
{
    protected $connection = 'sqlsrv';

    public $table = 'Ptype_Price';

    public $timestamps= false;
}
