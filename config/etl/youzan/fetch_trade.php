<?php
use \App\EtlRunRecord;
use \App\ETL\ETL;
use App\ETL\Input\YouZanApi;
use App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;

$identity = 'etl.youzan.fetch_trade';

return
    [
        'input' => function() use ($sql){
            return new YouZanApi(
                'youzan.trades.sold.get',
                '3.0.0',
                function ($data){
                    return array_get($data, 'trades', []);
                });
        },
        'output' => function(){
            $dc = \DB::connection('dc')->getPdo();

            $tradeColumns = ["tid", "num", "item_id", "price", "title", "type", "feedback", "refund_state", "outer_tid", "transaction_tid", "status", "post_fee", "total_fee", "refunded_fee", "discount_fee", "payment", "created", "update_time", "pay_time", "pay_type", "consign_time", "sign_time", "adjust_fee", "relation_type", "relations", "out_trade_no", "profit", "handled", "outer_user_id", "status_str", "box_price"];
            return new CompositeSerially([
                'fact_youzan_trade' => (new MysqlInsertUpdateWithPdo($dc,'fact_youzan_trade', $tradeColumns, $tradeColumns)),

            ], function ($items){
                $trades = $promotions = $coupons = $fans = $orders = [];

                foreach ($items as $item){
                    $item['adjust_fee'] = !empty($item['adjust_fee']) ? json_encode($item['adjust_fee']) : '';
                    $item['relations'] = !empty($item['relations']) ? json_encode($item['relations']) : '';
                    $item['out_trade_no'] = !empty($item['out_trade_no']) ? json_encode($item['out_trade_no']) : '';
                    $item['consign_time'] = !empty($item['consign_time']) ? $item['consign_time'] : '2000-01-01';
                    $item['sign_time'] = !empty($item['sign_time']) ? $item['sign_time'] : '2000-01-01';
                    $item['pay_time'] = !empty($item['pay_time']) ? $item['pay_time'] : '2000-01-01';
                    $item['handled'] = !empty($item['handled']) ? $item['handled'] : null;
                    $item['profit'] = !empty($item['profit']) ? $item['profit'] : null;

                    $trades[] = $item;
                }

                return [
                    'fact_youzan_trade' => $trades
                ];
            });
        },
        'before' => function (ETL $etl) use ($identity) {
            EtlRunRecord::createOrWake(
                $identity,
                $etl,
                function (EtlRunRecord $record=null, EtlRunRecord $lastRecord=null){
                    $record->params = [
                        'start_created' => '2018-02-06',
                        'end_created' => '2018-04-30'
                    ];
                    $record->marker = 1;
                },
                null
            );
        },
        'after' => function (ETL $etl) use ($identity){
            EtlRunRecord::endOrSleep($identity, $etl, function (EtlRunRecord $record){
                $record->marker = 1;
                $record->state = EtlRunRecord::STATE_RUNNING;

                $timeBegin = min(time(), strtotime($record->params['end_created']));
                $timeEnd = min(time(), strtotime('+1 day', $timeBegin));

                $record->params = [
                    'start_created' => date('Y-m-d H:i:s', $timeBegin),
                    'end_created' => date('Y-m-d H:i:s', $timeEnd),
                ];
            });
        },
        'fail' => function (ETL $etl, \Exception $e) use ($identity) {
            EtlRunRecord::fail($identity, $etl);
        },
        'limit' => 100,
        'upper' => 1000
    ];