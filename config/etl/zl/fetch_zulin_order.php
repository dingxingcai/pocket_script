<?php
use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;

$sql = <<<SQL
select 
'ZL'as 'business_type',
o.orderNo as 'business_id',
o.state as 'business_status',
o.createdAt as 'ts_created',
o.rentTotal,
o.deposit,
o.lateFee,
o.otherFee,
o.compensation,
o.discountAmount,
o.paymentTotal,
o.outStoreId as 'store_code',
o.outStaffId as 'sales_code',
u.phone as 'vip_telephone' from `order` o 
left join `user` u on u.id = o.customerId
where o.updatedAt BETWEEN :timeBegin AND :timeEnd LIMIT :limit offset :offset
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
                    ['oid', 'business_type', 'business_id', 'ts_created', 'business_status', 'vip_telephone','store_code', 'sales_code','price_actual', 'price_original', 'price_payed'],
                    ['business_status','price_actual','price_payed','price_original']),
                'exp' => new MysqlInsertUpdateWithPdo($dc, 'fact_exp_zulin',
                    ['oid', 'order_id'],
                    ['oid', 'order_id'])
            ], function ($aData){
                $res = ['order' => [], 'exp' => []];
                foreach ($aData as $data) {
                    empty($data['sales_code']) && $data['sales_code'] = '';
                    $data['oid'] = 'ZL' . $data['business_id'];

                    $data['price_original'] = $data['rentTotal'] + $data['deposit'];
                    $data['price_actual'] = $data['price_original'] - $data['discountAmount'];
                    $data['price_payed'] = $data['rentTotal'] + $data['deposit'] - $data['discountAmount'] + $data['lateFee'] + $data['compensation'] + $data['otherFee'];

                    $data['order_id'] = $data['business_id'];
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
                        'timeEnd' => '2018-04-25'
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
        'limit' => 30,
        'upper' => 300000
    ];