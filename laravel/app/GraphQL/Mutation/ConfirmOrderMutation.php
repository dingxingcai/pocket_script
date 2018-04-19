<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 19:06
 */

namespace App\GraphQL\Mutation;

use App\Library\Curl;
use App\NVipCardSign;
use App\Order;
use App\OrderDetail;
use App\PosInfo;
use App\PtypePrice;
use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use GraphQL;
use DB;
use Cache;
use Exception;
use App\Library\Helper;
use GraphQL\Type\Definition\ObjectType;


/*
 * 移动端下单
 * */

class ConfirmOrderMutation extends Mutation
{

    protected $attributes = [
        'name' => 'confirmOrder'
    ];


    public function type()
    {
        return GraphQL::type('return');
    }

    public function args()
    {
        return [
            'goods' => ['name' => 'goods', 'type' => Type::listOf(GraphQL::type('goodsQty'))],
            'vipNo' => ['name' => 'vipNo', 'type' => Type::string()],
            'nId' => ['name' => 'nId', Type::int()]
        ];
    }

    public function rules(array $args = [])
    {
        return [
            'nId' => 'required',
            'goods' => 'required'
        ];
    }

    public function resolve($root, $args)
    {


        $posInfo = Helper::posInfo();
        /** @var \App\User $user */
        $user = \JWTAuth::parseToken()->authenticate();

        $vipCardId = -1;
        $discount = 1;
        //查看是否是vip
        if (isset($args['vipNo']) && !empty($args['vipNo'])) {
            $vipCardSign = NVipCardSign::select('VipCardTypeID', 'VipCardID')->where('VipCardCode', $args['vipNo'])->first();
            if ($vipCardSign) {

                $vipCardId = $vipCardSign->VipCardID;
                $discount = Helper::getVipCount($vipCardSign->VipCardTypeID);
            }
        }

        $goods = $args['goods'];
        $totalMoney = 0;  //总金额
        $totalInMoney = 0;   //总优惠后金额
        $totalDisMoney = 0;  //总优惠金额
        $goodsPrices = array();
        $qty = 0;
        foreach ($goods as $good) {

            $price = PtypePrice::select('RetailPrice')->where('PtypeID', $good['typeId'])->first();
            if (!$price) {
                throw new Exception('商品不存在');
            }

            $goodsPrices [] = [
                'typeId' => $good['typeId'],
                'price' => $price->RetailPrice,
                'Qty' => $good['Qty'],
            ];

            $qty += $good['Qty'];

            $money = ($price->RetailPrice) * $good['Qty'];
            $Inmoney = ($price->RetailPrice) * $good['Qty'] * $discount;
            $disMoney = ($price->RetailPrice) * $good['Qty'] * (1 - $discount);
            $totalMoney += $money;
            $totalInMoney += $Inmoney;
            $totalDisMoney += $disMoney;
        }

        DB::beginTransaction();
        try {
            $orderId = date('YmdHis') . mt_rand(100, 999);
            //存入主订单
            $order = Order::create(
                [
                    "orderId" => $orderId,
                    'billDate' => date('Y-m-d H:i:s', time()),
                    'bTypeId' => $posInfo->BtypeID,    //往来单位
                    'eTypeId' => $user->uid,
                    'kTypeId' => $posInfo->ktypeid,
                    'totalMoney' => $totalMoney,
                    'totalInMoney' => round($totalInMoney, 2),
                    'discountMoney' => round($totalDisMoney, 2),
                    'discount' => $discount,
                    'vipCardId' => $vipCardId,
                    'Qty' => $qty,
                    'aTypeId' => trim($args['nId'])
                ]
            );

            //同步订单需要的商品书库
            $goods = [];

            //存储订单商品具体的信息
            foreach ($goodsPrices as $value) {

                OrderDetail::create(
                    [
                        'orderId' => $order->orderId,
                        'pTypeId' => $value['typeId'],
                        'Qty' => $value['Qty'],
                        'retailPrice' => $value['price'],
                        'discount' => $discount,
                        'discountPrice' => $value['price'] * $discount,
                        'totalMoney' => $value['price'] * $discount * $value['Qty']


                    ]
                );
                $goodsDetail = [
                    'pTypeId' => $value['typeId'],
                    'Qty' => $value['Qty'],
                    'retailPrice' => $value['price'],
                    'discount' => $discount,
                    'discountPrice' => $value['price'] * $discount,
                    'totalMoney' => $value['price'] * $discount * $value['Qty']
                ];

                $goods[] = $goodsDetail;

            }


        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        DB::commit();


        //同步订单给管家婆
        $key = config('app.syncKey');
        $url = config('app.syncUrl');
        $md5Param = "$key" . date('Y-m-d', time()) . "$vipCardId" . date('Ymd');
        $param = [
            'billDate' => date('Y-m-d', time()),
            'bTypeid' => $posInfo->BtypeID,
            'eTypeid' => $user->uid,
            'kTypeid' => $posInfo->ktypeid,
            'totalMoney' => $totalMoney,
            'totalInMoney' => round($totalInMoney, 2),
            'discountMoney' => round($totalDisMoney, 2),
            'discount' => $discount,
            'VipCardId' => $vipCardId,
            'Qty' => $qty,
            'aTypeId' => trim($args['nId']),
            'Data' => $goods,
            'signature' => strtoupper(md5($md5Param)),
        ];

        $result = Curl::curl($url, json_encode($param), true, false, true);

        if (!isset($result['Code']) || $result['Code'] != 1) {
            throw new Exception('录单失败,' . $result['Message']);
        }

        //修改order的status=1,同步订单成功
        Order::where('orderId', $orderId)->update(['status' => 1]);


        return ['msg' => '录单成功', 'code' => 200];

    }
}