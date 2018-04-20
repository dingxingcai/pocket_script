<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GoodsStock extends Model
{
    protected $connection = 'sqlsrv';

    public $table = 'GoodsStocks';

    public $timestamps = false;
}
