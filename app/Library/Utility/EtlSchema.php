<?php

namespace App\Utility;

class EtlSchema
{
    const TABLE_YOUZAN_TRADE = 'youzan_trade';
    const TABLE_STATIONS = 'ods_stations';
    const TABLE_EXPENSE_TYPES = 'ods_expense_types';
    const TABLE_USER_CAR_RELATIONS = 'ods_user_car_relations';
    const TABLE_DWD_CARS = 'dwd_cars';

    public static function schema($table)
    {
        switch ($table){
            case self::TABLE_YOUZAN_TRADE:
                return self::backup();

            case self::TABLE_STATIONS:
                return self::stations();

            case self::TABLE_EXPENSE_TYPES:
                return self::expenseTypes();

            case self::TABLE_USER_CAR_RELATIONS:
                return self::userCarRelations();

            case self::TABLE_CARS:
                return self::cars();

            case self::TABLE_FUEL_RECORDS:
                return self::fuelRecords();

            case self::TABLE_EXPENSES:
                return self::expenses();

            case self::TABLE_INSURANCE_REMINDERS:
                return self::insuranceReminders();

            case self::TABLE_MAINT_DATE_REMINDERS:
                return self::maintDateReminders();

            case self::TABLE_MAINT_ODOMETER_REMINDERS:
                return self::maintOdometerReminders();

            case self::TABLE_MAINT_PERIODICAL_REMINDERS:
                return self::maintPeriodicalReminders();

            case self::TABLE_DWD_CARS:
                return self::dwdCars();

            default:
                throw new \Exception("指定表{$table}不存在schema");
        }
    }

    public static function schemaMaps($tables, $postfix = '')
    {
        $res = [];
        foreach ($tables as $table){
            $res[$table . $postfix] = self::schema($table);
        }

        return $res;
    }

    private static function backup()
    {
        $defaultDate = EtlConstant::DEFAULT_MIN_DATE;

        $sql = <<<SQL
CREATE TABLE `%table%` (
  `n_id` bigint(20) AUTO_INCREMENT ,
  `userId` bigint(20) COMMENT 'userId',
  `devId` VARCHAR(512) DEFAULT '' COMMENT '',
  `appVer` INT DEFAULT 0 COMMENT '',
  `dataVer` INT DEFAULT 0 COMMENT '',
  `backupTime` INT DEFAULT 0 COMMENT '',
  `n_backupTime` timestamp DEFAULT '{$defaultDate}' COMMENT '',
  `size` INT DEFAULT 0 COMMENT '',
  `backupVersion` INT DEFAULT 0 COMMENT '',
  `odometer` INT DEFAULT 0 COMMENT '',
  `address` VARCHAR(128) DEFAULT '',
  `carrier` INT DEFAULT 0 COMMENT '',
  `city` VARCHAR(64) DEFAULT '',
  `coordinateType` VARCHAR(32) DEFAULT '',
  `country` VARCHAR(32) DEFAULT '',
  `detectedTime` INT DEFAULT 0 COMMENT '',
  `n_detectedTime` timestamp DEFAULT '{$defaultDate}' COMMENT '',
  `district` VARCHAR(64) DEFAULT '',
  `latitude` DOUBLE DEFAULT 0 COMMENT '',
  `locationType` INT DEFAULT 0 COMMENT '',
  `longitude` DOUBLE DEFAULT 0 COMMENT '',
  `province` VARCHAR(32) DEFAULT '',
  `streetName` VARCHAR(128) DEFAULT '',
  `streetNumber` VARCHAR(128) DEFAULT '',
  `n_ts_created` timestamp DEFAULT NOW(),

  `cars_count` INT DEFAULT 0 COMMENT '',
  `fuel_records_count` INT DEFAULT 0 COMMENT '',
  `fuel_records_json` VARCHAR(1024) DEFAULT '',
  `stations_count` INT DEFAULT 0 COMMENT '',
  `expense_types_count` INT DEFAULT 0 COMMENT '',

  `clientBackupVersion` INT DEFAULT 0,
  `maxClientBackupVersion` INT DEFAULT 0,
  `index` INT DEFAULT 0,
  PRIMARY KEY (`n_id`),
  KEY `idx_userId_backupVersion` (`userId`, `backupVersion`)
) ENGINE=innodb DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
SQL;

        return $sql;
    }

    private static function stations()
    {
        $defaultDate = EtlConstant::DEFAULT_MIN_DATE;

        $sql = <<<SQL
CREATE TABLE `%table%` (
  `n_id` bigint(20) AUTO_INCREMENT ,
  `id` bigint(20) DEFAULT 0 ,
  `userId` bigint(20) COMMENT 'userId',
  `latitudeE6` DOUBLE DEFAULT 0 COMMENT '',
  `longitudeE6` DOUBLE DEFAULT 0 COMMENT '',
  `address` VARCHAR(256) DEFAULT '',
  `city` VARCHAR(64) DEFAULT '',
  `name` VARCHAR(512) DEFAULT '',
  `phoneNum` VARCHAR(64) DEFAULT '',
  `postCode` VARCHAR(32) DEFAULT '',
  `timeStamp` BIGINT(20) DEFAULT 0 COMMENT '',
  `n_timeStamp` timestamp DEFAULT '{$defaultDate}' COMMENT '',
  `n_ts_created` timestamp DEFAULT NOW(),
  PRIMARY KEY (`n_id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_id` (`id`)
) ENGINE=myisam DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
SQL;

        return $sql;
    }

    private static function expenseTypes()
    {
        $sql = <<<SQL
CREATE TABLE `%table%` (
  `n_id` bigint(20) AUTO_INCREMENT ,
  `userId` bigint(20) COMMENT 'userId',
  `_id` INT DEFAULT 0,
  `color` bigint(20) ,
  `description` VARCHAR(64) DEFAULT '',
  `name` VARCHAR(64) DEFAULT '',
  `n_ts_created` timestamp NULL DEFAULT NOW(),
  PRIMARY KEY (`n_id`),
  KEY `idx_userId` (`userId`)
) ENGINE=myisam DEFAULT CHARSET=utf8
;
SQL;

        return $sql;
    }

    private static function userCarRelations()
    {
        $sql = <<<SQL
CREATE TABLE `%table%` (
  `n_id` bigint(20) AUTO_INCREMENT ,
  `userId` bigint(20) COMMENT 'userId',
  `uuid` bigint(20) COMMENT 'uuid',
  `n_ts_created` timestamp NULL DEFAULT NOW(),
  PRIMARY KEY (`n_id`),
  KEY `idx_userId` (`userId`),
  KEY `idx_uuid` (`uuid`)
) ENGINE=myisam DEFAULT CHARSET=utf8
;
SQL;

        return $sql;
    }

    private static function cars()
    {
        $defaultDate = EtlConstant::DEFAULT_MIN_DATE;

        $sql = <<<SQL
CREATE TABLE `%table%` (
  `n_id` bigint(20) AUTO_INCREMENT ,
  `uuid` bigint(20) NOT NULL COMMENT 'uuid',
  `_id` INT(20) NOT NULL DEFAULT '0' COMMENT '',
  `avgConsumption` DOUBLE DEFAULT NULL COMMENT '',
  `avg_cspt` DOUBLE DEFAULT NULL COMMENT '平均油耗',
  `model` int(10) DEFAULT NULL COMMENT '',
  `name` VARCHAR(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '',
  `selected` TINYINT DEFAULT NULL COMMENT '',
  `n_ts_created` timestamp DEFAULT NOW(),
  `avg_calculate_result` INT DEFAULT -1 COMMENT '油耗计算结果码',
  `avg_calculate_msg` VARCHAR(255) DEFAULT -1 COMMENT '油耗计算结果描述信息',
  `odometer` INT DEFAULT 0 COMMENT '总里程（里程表总读数）',
  `distance` INT DEFAULT 0 COMMENT '总行程（使用小熊油耗统计的里程数）',
  `total_liter` DOUBLE DEFAULT 0 COMMENT '总加油量',
  `total_fuel_payment` DOUBLE DEFAULT 0 COMMENT '总加油金额',
  `total_fuel_record_number` INT COMMENT '总加油记录数',
  `first_fuel_record_time` TIMESTAMP DEFAULT '{$defaultDate}' COMMENT '最早油耗记录时间',
  `last_fuel_record_time` TIMESTAMP DEFAULT '{$defaultDate}' COMMENT '最新油耗记录时间',
  
  `first_fuel_record_odometer` INT DEFAULT 0  COMMENT '首次油耗记录里程',
  
  `half_year_first_fuel_record_odometer` INT DEFAULT 0  COMMENT '半年后第一次油耗数',
  `half_year_first_fuel_record_time` TIMESTAMP DEFAULT '{$defaultDate}'  COMMENT '半年后弟一次油耗记录时间',
  
  `one_year_first_fuel_record_odometer` INT DEFAULT 0  COMMENT '一年后第一次油耗数',
  `one_year_first_fuel_record_time` TIMESTAMP DEFAULT '{$defaultDate}'  COMMENT '一年后弟一次油耗记录时间',
  
  `total_expense_record_number` INT COMMENT '费用记录数',
  `total_expense_payment` DOUBLE COMMENT '费用总支出',
  `first_expense_record_time` TIMESTAMP DEFAULT '{$defaultDate}' COMMENT '最早费用记录时间',
  `last_expense_record_time` TIMESTAMP DEFAULT '{$defaultDate}' COMMENT '最新费用记录时间',
  `bucket` VARCHAR(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '',
  `uri` VARCHAR(256) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '',
  PRIMARY KEY (`n_id`),
  KEY `uidx_uuid` (`uuid`)
) ENGINE=innodb DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
SQL;

        return $sql;
    }

    private static function dwdCars()
    {
        $defaultDate = EtlConstant::DEFAULT_MIN_DATE;

        $sql = <<<TEM
CREATE TABLE IF NOT EXISTS `%table%` (
  `uuid` bigint(20) NOT NULL COMMENT 'uuid',
  `car_name` VARCHAR(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '',
  `avg_cspt` DOUBLE DEFAULT NULL COMMENT '平均油耗',
  `avg_scpt_from_app` DOUBLE COMMENT '从app上报',
  `avg_scpt_from_backup` DOUBLE COMMENT '从备份数据上报',
  `avg_scpt_from_legacy_backup` DOUBLE COMMENT '遗留二进制数据上报',
  `source_adopted` TINYINT COMMENT '采信来源，1-app,2-backup,3-legacy_backup,4-unknown',

  `model_id` int(10) DEFAULT NULL COMMENT '',
  `country` VARCHAR(64) DEFAULT '' COMMENT '国家',
  `province` VARCHAR(64) DEFAULT '' COMMENT '省份',
  `city` VARCHAR(64) DEFAULT '' COMMENT '城市',
  `district` VARCHAR(64) DEFAULT '' COMMENT '地县',
  `street` VARCHAR(64) DEFAULT '' COMMENT '街道',
  `address` VARCHAR(64) DEFAULT '' COMMENT '地址',
  `longitude` VARCHAR(64) DEFAULT '' COMMENT '经度',
  `latitude` VARCHAR(64) DEFAULT '' COMMENT '纬度',
  `location_detection_time` TIMESTAMP COMMENT '地理定位时间',
  `location_detection_type` INT COMMENT '定位类型',
  `coordinate_type` INT COMMENT '地理坐标类型',
  `avg_calculate_result` INT DEFAULT -1 COMMENT '油耗计算结果码',
  `avg_calculate_msg` VARCHAR(255) DEFAULT -1 COMMENT '油耗计算结果描述信息',
  `odometer` INT DEFAULT 0 COMMENT '总里程（里程表总读数）',
  `distance` INT DEFAULT 0 COMMENT '总行程（使用小熊油耗统计的里程数）',
  `total_liter` DOUBLE DEFAULT 0 COMMENT '总加油量',
  `total_fuel_payment` DOUBLE DEFAULT 0 COMMENT '总加油金额',
  `total_fuel_record_number` INT COMMENT '总加油记录数',
  `first_fuel_record_time` TIMESTAMP COMMENT '最早油耗记录时间',
  `last_fuel_record_time` TIMESTAMP COMMENT '最新油耗记录时间',
  `total_expense_record_number` INT COMMENT '费用记录数',
  `total_expense_payment` DOUBLE COMMENT '费用总支出',
  `first_expense_record_time` TIMESTAMP COMMENT '最早费用记录时间',
  `last_expense_record_time` TIMESTAMP COMMENT '最新费用记录时间',
  
  `first_fuel_record_odometer` INT DEFAULT 0  COMMENT '首次油耗记录里程',
  
  `half_year_first_fuel_record_odometer` INT DEFAULT 0  COMMENT '半年后第一次油耗数',
  `half_year_first_fuel_record_time` TIMESTAMP DEFAULT '{$defaultDate}'  COMMENT '半年后弟一次油耗记录时间',
  
  `one_year_first_fuel_record_odometer` INT DEFAULT 0  COMMENT '一年后第一次油耗数',
  `one_year_first_fuel_record_time` TIMESTAMP DEFAULT '{$defaultDate}'  COMMENT '一年后弟一次油耗记录时间',
  
  `total_expense_record_number` INT COMMENT '费用记录数',
  `total_expense_payment` DOUBLE COMMENT '费用总支出',
  
  `ts_created` timestamp DEFAULT NOW(),
  PRIMARY KEY (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
TEM;

        return $sql;
    }

    private static function fuelRecords()
    {
        $sql = <<<SQL
CREATE TABLE `%table%` (
`n_id` bigint(20) AUTO_INCREMENT ,
  `uuid` bigint(20) NOT NULL COMMENT 'uuid',
  `carId` INT(10) DEFAULT NULL COMMENT '',
  `odometer` INT(20) DEFAULT NULL DEFAULT '0' COMMENT '里程',
  `stationId` bigint(20) DEFAULT NULL COMMENT '',
  `consumption` DOUBLE DEFAULT NULL COMMENT '油耗',
  `type` bigint(20) DEFAULT NULL COMMENT '',
  `price` DOUBLE DEFAULT NULL COMMENT '单价',
  `yuan` DOUBLE DEFAULT NULL COMMENT '总价',
  `forget` TINYINT DEFAULT NULL COMMENT '是否忘记上次',
  `remark` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `date` BIGINT(20) DEFAULT NULL COMMENT '加油时间',
  `lightOn` TINYINT DEFAULT NULL COMMENT '是否亮灯',
  `gassup` TINYINT DEFAULT NULL COMMENT '是否加满',
  `n_fuel_liter` DOUBLE DEFAULT NULL COMMENT '加油数，从yuan和price计算而来',
  `n_date` TIMESTAMP COMMENT '从_date计算出来',
  `n_ts_created` timestamp DEFAULT NOW(),
  PRIMARY KEY (`n_id`),
  KEY `idx_uuid` (`uuid`),
  KEY `idx_n_date` (`n_date`),
  KEY `idx_dometer` (`odometer`)
) ENGINE=innodb DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
SQL;

        return $sql;
    }

    private static function expenses()
    {
        $sql = <<<SQL
CREATE TABLE `%table%` (
`n_id` bigint(20) AUTO_INCREMENT ,
  `uuid` bigint(20) NOT NULL COMMENT 'uuid',
  `carId` INT(10) DEFAULT NULL COMMENT '',
  `type` VARCHAR(256) DEFAULT NULL COMMENT '',
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `date` BIGINT(20) DEFAULT NULL COMMENT '加油时间',
  `expense` DOUBLE DEFAULT NULL COMMENT '总价',
  `n_date` TIMESTAMP COMMENT '从_date计算出来',
  `n_ts_created` timestamp DEFAULT NOW(),
  PRIMARY KEY (`n_id`),
  KEY `idx_uuid` (`uuid`),
  KEY `idx_date` (`n_date`)
) ENGINE=innodb DEFAULT CHARSET=utf8
;
SQL;

        return $sql;
    }

    private static function insuranceReminders()
    {
        $sql = <<<SQL
CREATE TABLE `%table%` (
`n_id` bigint(20) AUTO_INCREMENT ,
  `uuid` bigint(20) NOT NULL COMMENT 'uuid',
  `nextBuyDate` BIGINT(20) DEFAULT NULL COMMENT '',
  `on` TINYINT(10) DEFAULT NULL COMMENT '',
  `reminderDays` INT(10) DEFAULT NULL COMMENT '',
  `n_nextBuyDate` TIMESTAMP COMMENT '从nextBuyDate计算出来',
  `n_ts_created` timestamp DEFAULT NOW(),
  PRIMARY KEY (`n_id`),
  KEY `idx_uuid` (`uuid`),
  KEY `idx_nextBuyDate` (`n_nextBuyDate`)
) ENGINE=innodb DEFAULT CHARSET=utf8
;
SQL;

        return $sql;
    }

    private static function maintDateReminders()
    {
        $defaultDate = EtlConstant::DEFAULT_MIN_DATE;

        $sql = <<<SQL
CREATE TABLE `%table%` (
`n_id` bigint(20) AUTO_INCREMENT ,
  `uuid` bigint(20) NOT NULL COMMENT 'uuid',
  `lastMaintDate` BIGINT(20) DEFAULT NULL COMMENT '',
  `monInterval` INT(10) DEFAULT NULL COMMENT '',
  `nextMaintDate` BIGINT(20) DEFAULT NULL COMMENT '',
  `on` TINYINT(10) DEFAULT NULL COMMENT '',
  `reminderDays` INT(10) DEFAULT NULL COMMENT '',
  `n_lastMaintDate` TIMESTAMP DEFAULT '{$defaultDate}' COMMENT '从lastMaintDate计算出来',
  `n_nextMaintDate` TIMESTAMP DEFAULT '{$defaultDate}' COMMENT '从nextMaintDate计算出来',
  `n_ts_created` timestamp DEFAULT NOW(),
  PRIMARY KEY (`n_id`),
  KEY `idx_uuid` (`uuid`)
) ENGINE=innodb DEFAULT CHARSET=utf8
;
SQL;

        return $sql;
    }

    private static function maintOdometerReminders()
    {
        $defaultDate = EtlConstant::DEFAULT_MIN_DATE;

        $sql = <<<SQL
CREATE TABLE `%table%` (
`n_id` bigint(20) AUTO_INCREMENT ,
  `uuid` bigint(20) NOT NULL COMMENT 'uuid',
  `avgDailyDistance` INT(10) DEFAULT NULL COMMENT '',
  `lastOdometer` INT(10) DEFAULT NULL COMMENT '',
  `nextMaintDate` BIGINT(20) DEFAULT NULL COMMENT '',
  `nextMaintOdometer` INT(10) DEFAULT NULL COMMENT '',
  `on` TINYINT(10) DEFAULT NULL COMMENT '',
  `reminderDays` INT(10) DEFAULT NULL COMMENT '',
  `n_nextMaintDate` TIMESTAMP DEFAULT '{$defaultDate}' COMMENT '从nextMaintDate计算出来',
  `n_ts_created` timestamp DEFAULT NOW(),
  PRIMARY KEY (`n_id`),
  KEY `idx_uuid` (`uuid`)
) ENGINE=innodb DEFAULT CHARSET=utf8
;
SQL;

        return $sql;
    }

    private static function maintPeriodicalReminders()
    {
        $defaultDate = EtlConstant::DEFAULT_MIN_DATE;

        $sql = <<<SQL
CREATE TABLE `%table%` (
`n_id` bigint(20) AUTO_INCREMENT ,
  `uuid` bigint(20) NOT NULL COMMENT 'uuid',
  `avgDailyDistance` INT(10) DEFAULT NULL COMMENT '',
  `lastMaintOdometer` INT(10) DEFAULT NULL COMMENT '',
  `lastOdometer` INT(10) DEFAULT NULL COMMENT '',
  `maintDistance` INT(10) DEFAULT NULL COMMENT '',
  `nextMaintDate` BIGINT(20) DEFAULT NULL COMMENT '',
  `nextMaintOdometer` INT(10) DEFAULT NULL COMMENT '',
  `on` TINYINT(10) DEFAULT NULL COMMENT '',
  `reminderDays` INT(10) DEFAULT NULL COMMENT '',
  `n_nextMaintDate` TIMESTAMP DEFAULT '{$defaultDate}' COMMENT '从nextMaintDate计算出来',
  `n_ts_created` timestamp DEFAULT NOW(),
  PRIMARY KEY (`n_id`),
  KEY `idx_uuid` (`uuid`)
) ENGINE=innodb DEFAULT CHARSET=utf8
;
SQL;

        return $sql;
    }
}