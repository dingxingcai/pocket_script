<?php

use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;
use App\Library\Helper;

$sql = <<<SQL
select o.*,
p.refundFee as 'refundMoney',
p.createdAt as 'refundTime',
cc.couponId from `order` o 
left join paymentrefund p on p.id = o.paymentRefundId
left join ordercustomercoupon c on o.id = c.orderId
left join customercoupon cc on cc.id = c.customerCouponId
where o.updatedAt BETWEEN  :timeBegin and :timeEnd limit :limit offset :offset;
SQL;

$identity = EtlConstant::FETCH_ORDER_EXP;

return
    [
        'input' => function () use ($sql) {
            return new PdoWithLaravel('zulin', $sql);
        },
        'output' => function () {
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'order' => new MysqlInsertUpdateWithPdo($dc, 'zulin_order',
                    ['orderNo', 'ts_created', 'goodsId', 'itemId', 'customerId', 'storeId', 'actualOutAt', 'outAt', 'actualInAt', 'InAt', 'outStaff', 'inStaff',
                        'cancleTime', 'rentTotal', 'deposit', 'originalPrice', 'discountPrice', 'couponId', 'lateDays', 'useLength', 'actualUseLength', 'refundTime', 'refundMoney',
                        'finishedAt', 'damage', 'settleAt'],
                    ['itemId', 'actualOutAt', 'outAt', 'actualInAt', 'outStaff', 'lateDays', 'actualUseLength', 'settleAt','refundMoney','damage','finishedAt'])
            ], function ($aData) {
                $res = ['order' => []];
                foreach ($aData as $data) {
                    $data['ts_created'] = $data['createdAt'];
                    $data['storeId'] = $data['outStoreId'];
                    $data['InAt'] = $data['inAt'];
                    $data['outStaff'] = $data['outStaffId'];
                    $data['inStaff'] = $data['inStaffId'];
                    $data['originalPrice'] = $data['rentTotal'] + $data['deposit'];
                    $data['discountPrice'] = $data['discountAmount'];
                    $data['useLength'] = Helper::getDiffDate($data['outAt'], $data['inAt']);
                    $data['actualUseLength'] = Helper::getDiffDate($data['actualOutAt'], $data['actualInAt']);
                    $data['finishedAt'] = '';
                    if ($data['state'] == 'FINISHED') {
                        $data['finishedAt'] = $data['updatedAt'];   //订单完成时间
                    }

                    $data['cancleTime'] = '';
                    if ($data['state'] == 'CLOSED') {
                        $data['cancleTime'] = $data['updatedAt'];   //订单取消时间
                    }

                    $data['settleAt'] = $data['settlementFinishAt'];
                    $data['damage'] = $data['compensationReason'];

//                    $data['price_original'] = $data['rentTotal'] + $data['deposit'];
//                    $data['price_actual'] = $data['price_original'] - $data['discountAmount'];
//                    $data['price_payed'] = $data['paymentTotal'];
//                    $data['order_id'] = $data['business_id'];
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
                        'timeBegin' => '2018-04-01',
                        'timeEnd' => '2018-04-25'
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