<?php
use \App\EtlRunRecord;
use App\Utility\EtlConstant;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;

$identity = '';

$sql = <<<SQL

SELECT * FROM Events WHERE update_time BETWEEN :timeBegin AND :timeEnd LIMIT :limit offset :offset;

SQL;


return
    [
        'input' => function() use ($sql){
            return new PdoWithLaravel('business', $sql);
        },
        'output' => function(){
            return new MysqlInsertUpdateWithPdo(
                DB::connection('dc')->getPdo(),
                'odsb_events',
                ['id', 'userId', 'eid', 'beginTime', 'endTime', 'count', 'intTag1', 'intTag2', 'intTag3', 'strTag1', 'strTag2', 'strTag3', 'update_time'],
                ['eid', 'endTime', 'count', 'intTag1', 'intTag2', 'intTag3', 'strTag1', 'strTag2', 'strTag3', 'update_time']
            );
        },
        'before' => function (ETL $etl) use ($identity) {
            EtlRunRecord::createOrWake(
                $identity,
                $etl,
                function (EtlRunRecord $record=null, EtlRunRecord $lastRecord=null){
                    $record->params = [
                        'timeBegin' => '2017-11-21',
                        'timeEnd' => '2017-11-21 01:00'
                    ];
                    $record->marker = 0;

                },
                null
            );
        },
        'after' => function (ETL $etl) use ($identity){
            EtlRunRecord::endOrSleep($identity, $etl, function (EtlRunRecord $record){
                $record->marker = 0;

                $record->state = EtlRunRecord::STATE_RUNNING;

                $timeBegin = min(time(), strtotime($record->params['timeEnd']));
                $timeEnd = min(time(), strtotime('+1 hour', $timeBegin));

                $record->params = [
                    'timeBegin' => date('Y-m-d H:i:s', $timeBegin),
                    'timeEnd' => date('Y-m-d H:i:s', $timeEnd)
                ];
            });
        },
        'fail' => function (ETL $etl, \Exception $e) use ($identity) {
            EtlRunRecord::fail($identity, $etl);
        },
        'limit' => 1000,
        'upper' => 10000
    ];