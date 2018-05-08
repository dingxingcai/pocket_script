<?php
use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;

$sql = <<<SQL
select 
p.*
from  paymentrefund p
where p.updatedAt BETWEEN :timeBegin AND :timeEnd LIMIT :limit offset :offset
;
SQL;

$identity = EtlConstant::FETCH_PAYMENTREFUND;

return
    [
        'input' => function() use ($sql){
            return new PdoWithLaravel('zulin', $sql);
        },
        'output' => function(){
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'paymentrefund' => new MysqlInsertUpdateWithPdo($dc, 'paymentrefund',
                    ['id', 'paymentId', 'refundNo', 'refundFee', 'state', 'remark','createdAt', 'updatedAt','deletedAt'],
                    ['refundFee','remark','paymentId'])
            ], function ($aData){
                $res = ['paymentrefund' => []];
                foreach ($aData as $data) {
                    $res['paymentrefund'][] = $data;
                }
                return $res;
            });
        },
        'before' => function (ETL $etl) use ($identity) {
            EtlRunRecord::createOrWake(
                $identity,
                $etl,
                function (EtlRunRecord $record=null, EtlRunRecord $lastRecord=null){
                    $record->params = [
                        'timeBegin' => '2018-01-01',
                        'timeEnd' => '2018-05-08 13:40:00'
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
                $timeEnd = strtotime('+5 minute', $timeBegin);

                $record->params = [
                    'timeBegin' => date('Y-m-d H:i:s', $timeBegin),
                    'timeEnd' => date('Y-m-d H:i:s', $timeEnd)
                ];
            });
        },
        'fail' => function (ETL $etl, \Exception $e) use ($identity) {
            EtlRunRecord::fail($identity, $etl);
        },
        'limit' => 30,
        'upper' => 300000
    ];