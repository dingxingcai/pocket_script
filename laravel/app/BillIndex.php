<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillIndex extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'billindex';

    public $timestamps = false;


    public function retailBills()
    {
        return $this->hasMany(RetailBill::class, 'BillNumberId', 'BillNumberId');
    }
}
