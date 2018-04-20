<?php

$schema = <<<TEM
CREATE TABLE IF NOT EXISTS `dim_uuid` (
  `uuid` bigint(20) NOT NULL COMMENT 'uuid',
  `ts_created` timestamp DEFAULT NOW(),
  PRIMARY KEY (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
TEM;

$sql = <<<SQL
    INSERT INTO `dim_uuid` (uuid)
    SELECT * FROM (
    SELECT uuid FROM ods_cars_rebuild
    UNION
    SELECT uuid FROM ods_AvgConsumptionAll
    )a ORDER BY uuid ASC
SQL;

return (new \App\ETL\Suite\PdoSqlExecWithStaging())->create('dc', 'dim_uuid', $schema, $sql);