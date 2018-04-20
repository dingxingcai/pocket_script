<?php

use Illuminate\Database\Migrations\Migration;

class AddColumnIsCleanedToTableEtlRunRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = <<<SQL
    ALTER TABLE etl_run_record ADD COLUMN is_cleaned TINYINT DEFAULT 0 COMMENT '是否清理';
    UPDATE etl_run_record SET is_cleaned=1 WHERE state='clean';
SQL;

        DB::connection('dc')->unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = <<<SQL
        ALTER TABLE etl_run_record DROP COLUMN is_cleaned ;
SQL;

        DB::connection('dc')->unprepared($sql);
    }
}
