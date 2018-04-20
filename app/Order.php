<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $connection = 'mysql';

    public $table = 'order';

    public $timestamps = false;

    protected $fillable = [
        'orderId','billDate','bTypeId','eTypeId','kTypeId','totalMoney','totalInMoney','discountMoney','discount','vipCardId','aTypeId','Qty'
    ];
}
