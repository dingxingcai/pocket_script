<?php

use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertWithPdo;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;

$sql = <<<SQL
select top :limit  *  from 
(select ROW_NUMBER() OVER (ORDER BY r.BillNumberId asc, r.PtypeId asc) AS RowNumber,
  r.BillNumberId ,
  r.PtypeId ,
  r.Qty ,
  r.SalePrice ,
  r.discount ,
  r.DiscountPrice ,
  r.costprice ,
  r.total ,
  r.TaxPrice ,
  r.TaxTotal ,
  r.comment ,
  r.KTypeID ,
  r.ETypeID ,
  r.ID
FROM BillIndex b LEFT JOIN retailbill r 
on r.BillNumberId = b.BillNumberId 
WHERE b.BillType = 305  
and  b.posttime BETWEEN  :timeBegin and :timeEnd ) as A  WHERE 
A.RowNumber > (:offset - 1) order by A.RowNumber asc
;
SQL;

$identity = EtlConstant::FETCH_RETAILBILL_ORDER;

return
    [
        'input' => function () use ($sql) {
            return new PdoWithLaravel('gjp', $sql, 1);
        },
        'output' => function () {
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'retailbill' => new MysqlInsertUpdateWithPdo($dc, 'retailbill',
                    ['BillNumberId', 'PtypeId', 'Qty', 'SalePrice','ID', 'discount', 'DiscountPrice', 'costprice', 'total', 'TaxPrice', 'TaxTotal', 'comment', 'KTypeID', 'ETypeID'],
                    ['DiscountPrice', 'SalePrice', 'total'])
//                'retailbill' => new MysqlInsertWithPdo($dc, 'retailbill',
//                    ['BillNumberId', 'PtypeId', 'Qty', 'SalePrice','ID', 'discount', 'DiscountPrice', 'costprice', 'total', 'TaxPrice', 'TaxTotal', 'comment', 'KTypeID', 'ETypeID'])
            ], function ($aData) {
                $res = ['retailbill' => []];
                foreach ($aData as $data) {
                    $res['retailbill'][] = $data;
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
//                        'timeEnd' => date('Y-m-d H:i:s')
                        'timeEnd' => '2018-05-08 13:40:00'
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
        'limit' => 300,
        'upper' => 300000
    ];