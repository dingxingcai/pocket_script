<?php

use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;


$sql = <<<SQL
select top :limit * from  
(select ROW_NUMBER() OVER (ORDER BY b.BillNumberId asc) AS RowNumber,
'GJP' as business_type,
b.billnumberId as 'business_id',
p.UserCode as 'sku_code',
r.Qty as 'quantity',
r.SalePrice as 'price_original',
r.DiscountPrice as 'price_actual',
r.total as 'price_payed'
FROM BillIndex b 
inner join retailBill r on r.BillNumberId = b.BillNumberId 
inner join ptype p on r.PtypeId = p.typeId  
WHERE b.BillType = 305 and  b.posttime BETWEEN  :timeBegin and :timeEnd)  as A  WHERE 
A.RowNumber > (:offset -1)
;
SQL;

$identity = EtlConstant::FETCH_GJP_SKU_ORDER;

return
    [
        'input' => function () use ($sql) {
            return new PdoWithLaravel('gjp', $sql, 1);
        },
        'output' => function () {
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'sku' => new MysqlInsertWithPdo($dc, 'fact_order_sku',
                    ['oid', 'business_type', 'sku_code', 'quantity', 'price_actual', 'price_original', 'price_payed'])
            ], function ($aData) {
                $res = ['sku' => []];
                foreach ($aData as $data) {
                    $data['oid'] = 'GJP' . $data['business_id'];
                    $data['business_type'] = 'GJP';
                    $res['sku'][] = $data;
                }
                return $res;
            });
        },
        'before' => function (ETL $etl) use ($identity) {
            EtlRunRecord::createOrWake(
                $identity,
                $etl,
                function (EtlRunRecord $record = null, EtlRunRecord $lastRecord = null) {
                    $record->params = [
                        'timeBegin' => '2018-01-01 00:00:00',
                        'timeEnd' => '2018-04-24 12:00:00'
                    ];
                    $record->marker = 1;

                },
                null
            );
        },
        'after' => function (ETL $etl) use ($identity) {
            EtlRunRecord::endOrSleep($identity, $etl, function (EtlRunRecord $record) {
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
        'limit' => 300,
        'upper' => 300000
    ];