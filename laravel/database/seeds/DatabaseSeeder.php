<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('test')->insert([
            'id' => 2,
            'name' => '66655555',
            'time' => date('Y-m-d H:i:s',time()),
            'telephone' => '185',
            'sex2' => '女'
        ]);
    }
}
