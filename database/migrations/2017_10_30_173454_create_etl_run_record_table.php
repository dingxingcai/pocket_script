<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEtlRunRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $defaultDate = \App\Utility\EtlConstant::DEFAULT_MIN_DATE;
        $sql = <<<SQL
CREATE TABLE `etl_run_record` (
  `id` int(10) AUTO_INCREMENT ,
  `identity` VARCHAR(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'etl名称标识',
  `marker` TEXT COMMENT '比较字段',
  `state` VARCHAR(64) COMMENT '状态值',
  `etl_snapshot` TEXT COMMENT '快照的序列化后数据etl配置',
  `total_loaded` bigint(20) COMMENT '全部加载数量',
  `params` TEXT COMMENT '参数值',
  `stage` TEXT COMMENT '临时存储',
  `start_time` TIMESTAMP DEFAULT '{$defaultDate}' COMMENT '开始时间',
  `end_time` TIMESTAMP DEFAULT '{$defaultDate}' COMMENT '结束时间',
  `ts_updated` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后一次更新时间',
  `ts_created` timestamp DEFAULT NOW() COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=innodb DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
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
        Schema::dropIfExists('etl_run_record');
    }
}
