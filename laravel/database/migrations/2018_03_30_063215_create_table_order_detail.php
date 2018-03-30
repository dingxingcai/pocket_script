<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOrderDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderDetail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('orderId');
            $table->string('pTypeId', 32);
            $table->integer('Qty');
            $table->float('retailPrice');
            $table->float('discount');
            $table->float('discountPrice');
            $table->float('totalMoney');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orderDetail');
    }
}
