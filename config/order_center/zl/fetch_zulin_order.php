<?php

use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;

$sql = <<<SQL
select 
o.*
from  `order` o
where o.updatedAt BETWEEN :timeBegin AND :timeEnd LIMIT :limit offset :offset
;
SQL;

$identity = EtlConstant::FETCH_ZULINORDER;

return
    [
        'input' => function () use ($sql) {
            return new PdoWithLaravel('zulin', $sql);
        },
        'output' => function () {
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'order' => new MysqlInsertUpdateWithPdo($dc, 'zulinOrder',
                    ['id', 'orderNo', 'customerId', 'outStoreId', 'outAt', 'actualOutAt', 'inStoreId', 'inAt', 'actualInAt', 'goodsId', 'rent',
                        'rentTotal', 'rentedDays', 'actualRentedDays', 'deposit', 'lateFee', 'compensation', 'otherFee', 'otherReason', 'remark', 'state', 'paymentTotal',
                        'paymentFinishAt', 'paymentId', 'settlementTotal', 'settlementFinishAt', 'paymentRefundId', 'createdAt', 'updatedAt', 'itemId', 'archivedBy', 'settlementType',
                        'imageUrls', 'discountAmount', 'compensationReason', 'otherResult', 'lateDays', 'outStaffId', 'inStaffId', 'hasReturnedBackCoupon'],
                    ['customerId', 'outStoreId', 'outAt', 'actualOutAt', 'inStoreId', 'inAt', 'actualInAt', 'goodsId', 'rent',
                        'rentTotal', 'rentedDays', 'actualRentedDays', 'deposit', 'lateFee', 'compensation', 'otherFee', 'otherReason', 'remark', 'state', 'paymentTotal',
                        'paymentFinishAt', 'paymentId', 'settlementTotal', 'settlementFinishAt', 'paymentRefundId', 'createdAt', 'updatedAt', 'itemId', 'archivedBy', 'settlementType',
                        'imageUrls', 'discountAmount', 'compensationReason', 'otherResult', 'lateDays', 'outStaffId', 'inStaffId', 'hasReturnedBackCoupon'])
            ], function ($aData) {
                $res = ['order' => []];
                foreach ($aData as $data) {
                    $res['order'][] = $data;
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
                        'timeBegin' => '2018-01-01',
                        'timeEnd' => '2018-05-08 13:40:00'
                    ];
                    $record->marker = 0;

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
        'limit' => 30,
        'upper' => 300000
    ];