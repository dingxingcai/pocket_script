<?php
use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;

$sql = <<<SQL
SELECT 
'ZULIN' as business_type,
o.id as business_id,
o.id as order_id,
o.customerId,
o.inStoreId,
o.outStoreId,
o.paymentId,
o.goodsId,
g.code,
u.phone AS vip_telephone,
o.state AS business_status,
o.createdAt AS ts_created,
o.updatedAt 
FROM `order` o 
LEFT JOIN goods g ON o.goodsId=g.id
LEFT JOIN `user` u ON o.customerId=u.id
WHERE o.updatedAt BETWEEN :timeBegin AND :timeEnd LIMIT :limit offset :offset
;
SQL;

$identity = EtlConstant::FETCH_ZULIN_ORDER;

return
    [
        'input' => function() use ($sql){
            return new PdoWithLaravel('zulin', $sql);
        },
        'output' => function(){
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'order' => new MysqlInsertUpdateWithPdo($dc, 'fact_order',
                    ['oid', 'business_type', 'business_id', 'ts_created', 'business_status', 'vip_telephone', 'sales_code'],
                    ['business_status']),
                'exp' => new MysqlInsertUpdateWithPdo($dc, 'fact_exp_zulin',
                    ['oid', 'order_id'],
                    ['oid', 'order_id'])
            ], function ($aData){
                $res = ['order' => [], 'exp' => []];
                foreach ($aData as $data) {
                    empty($data['sales_code']) && $data['sales_code'] = '';
                    $data['oid'] = 'ZULIN' . $data['business_id'];
                    $res['order'][] = $data;
                    $res['exp'][] = $data;
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
                        'timeBegin' => '2018-04-01',
                        'timeEnd' => '2018-04-21'
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
                $timeEnd = min(time(), strtotime('+1 day', $timeBegin));

                $record->params = [
                    'timeBegin' => date('Y-m-d H:i:s', $timeBegin),
                    'timeEnd' => date('Y-m-d H:i:s', $timeEnd)
                ];
            });
        },
        'fail' => function (ETL $etl, \Exception $e) use ($identity) {
            EtlRunRecord::fail($identity, $etl);
        },
        'limit' => 1,
        'upper' => 2
    ];