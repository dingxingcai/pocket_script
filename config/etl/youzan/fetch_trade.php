<?php
use \App\EtlRunRecord;
use \App\ETL\ETL;
use App\ETL\Input\YouZanApi;
use App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use App\Library\Utility\EtlHelper;

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
            $fansColumns = ["tid", "fans_nickname", "fans_id", "buyer_id", "fans_type", "fans_weixin_openid"];
            $orderColumns = ["tid", "oid", "item_id", "sku_id", "sku_unique_code", "num", "outer_sku_id", "outer_item_id", "title", "seller_nick", "fenxiao_price", "fenxiao_payment", "price", "total_fee", "discount_fee", "payment", "sku_properties_name", "pic_path", "pic_thumb_path", "item_type", "buyer_messages", "order_promotion_details", "state_str", "item_refund_state", "is_virtual", "is_present", "refunded_fee", "allow_send", "is_send"];
            $couponColumns = ["coupon_id", "tid", "coupon_name", "coupon_type", "coupon_content", "coupon_description", "coupon_condition", "used_at", "discount_fee"];
            $promotionColumns = ["tid", "promotion_id", "promotion_name", "promotion_type", "promotion_condition", "used_at", "discount_fee"];
            return new CompositeSerially([
                'fact_youzan_trade' => (new MysqlInsertUpdateWithPdo($dc,'fact_youzan_trade', $tradeColumns, $tradeColumns)),
                'fact_youzan_trade_fans_info' => (new MysqlInsertUpdateWithPdo($dc,'fact_youzan_trade_fans_info', $fansColumns, $fansColumns)),
                'fact_youzan_trade_orders' => (new MysqlInsertUpdateWithPdo($dc,'fact_youzan_trade_orders', $orderColumns, $orderColumns)),
                'fact_youzan_trade_coupons' => (new MysqlInsertUpdateWithPdo($dc, 'fact_youzan_trade_coupons', $couponColumns, $couponColumns)),
                'fact_youzan_trade_pormotions' => (new MysqlInsertUpdateWithPdo($dc, 'fact_youzan_trade_pormotions', $promotionColumns, $promotionColumns)),

            ], function ($items){
                $trades = $promotions = $coupons = $fans = $orders = [];

                foreach ($items as $item){
                    $item['handled'] = !empty($item['handled']) ? $item['handled'] : null;
                    $item['profit'] = !empty($item['profit']) ? $item['profit'] : null;
                    EtlHelper::cleanJsonForArray($item, ['adjust_fee', 'relations', 'out_trade_no']);
                    EtlHelper::cleanDatetimeForArray($item, ['consign_time', 'sign_time', 'pay_time']);

                    $trades[] = $item;

//                    处理fans信息
                    if(!empty($item['fans_info'])){
                        $item['fans_info']['tid'] = $item['tid'];
                        $fans[] = $item['fans_info'];
                    }

//                    处理orders信息
                    if(!empty($item['orders'])){
                        foreach ($item['orders'] as $order){
                            EtlHelper::cleanJsonForArray($order, ['buyer_messages', 'order_promotion_details']);

                            $order['tid'] = $item['tid'];
                            $orders[] = $order;
                        }
                    }

//                     处理coupons信息
                    if(!empty($item['coupon_details'])){
                        foreach ($item['coupon_details'] as $coupon){
                            $coupon['tid'] = $item['tid'];
                            EtlHelper::cleanDatetimeForArray($coupon, 'used_at');
                            $coupons[] = $coupon;
                        }
                    }

//                     处理promotions信息
                    if(!empty($item['promotion_details'])){
                        foreach ($item['promotion_details'] as $promotion){
                            $promotion['tid'] = $item['tid'];
                            EtlHelper::cleanDatetimeForArray($promotion, 'used_at');
                            $promotions[] = $promotion;
                        }
                    }
                }

                return [
                    'fact_youzan_trade' => $trades,
                    'fact_youzan_trade_fans_info' => $fans,
                    'fact_youzan_trade_orders' => $orders,
                    'fact_youzan_trade_coupons' => $coupons,
                    'fact_youzan_trade_pormotions' => $promotions
                ];
            });
        },
        'before' => function (ETL $etl) use ($identity) {
            EtlRunRecord::createOrWake(
                $identity,
                $etl,
                function (EtlRunRecord $record=null, EtlRunRecord $lastRecord=null){
                    $record->params = [
                        'start_update' => !empty($lastRecord) ? $lastRecord->params['end_update'] : '2018-02-06',
                        'end_update' => date("Y-m-d H:i:s")
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

                $timeBegin = min(time(), strtotime($record->params['end_update']));
                $timeEnd = strtotime('+5 minute', $timeBegin);

                $record->params = [
                    'start_update' => date('Y-m-d H:i:s', $timeBegin),
                    'end_update' => date('Y-m-d H:i:s', $timeEnd),
                ];

            });
        },
        'fail' => function (ETL $etl, \Exception $e) use ($identity) {
            EtlRunRecord::fail($identity, $etl);
        },
        'limit' => 100,
        'upper' => 10000
//        'limit' => 2,
//        'upper' => 10
    ];