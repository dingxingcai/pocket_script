<?php

namespace Tests\Unit;

use App\BillIndex;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGroup()
    {
        $query = BillIndex::where(
            'BillType', 305
        )->whereHas('retailBills', function ($q){
            $q->where('ETypeID', '=', '000010023900012');
        })->limit(10)->get()
        ;

        $count = $query->count();
    }
}
