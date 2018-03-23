<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetailBill extends Model
{
    protected $connection = 'sqlsrv';

    public $table = 'retailbill';

    public $timestamps = false;
}
