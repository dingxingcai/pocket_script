<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->integer('orderId');
            $table->dateTime('billDate');
            $table->string('eTypeId', 32);
            $table->string('bTypeId', 32);
            $table->string('kTypeId', 32);
            $table->float('totalMoney');
            $table->float('totalInMoney');
            $table->float('discountMoney');
            $table->float('discount');
            $table->integer('Qty');
            $table->string('VipCardId', 32);
            $table->string('aTypeId', 32);
            $table->primary('orderId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order');
    }
}
