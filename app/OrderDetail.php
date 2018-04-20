<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $connection = 'mysql';

    public $table = 'orderDetail';

    public $timestamps = false;

    protected $fillable = [
        'orderId', 'pTypeId', 'Qty', 'retailPrice', 'discount', 'discountPrice', 'totalMoney'
    ];
}
