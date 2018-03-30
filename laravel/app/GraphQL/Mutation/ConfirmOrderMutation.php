<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 19:06
 */

namespace App\GraphQL\Mutation;

use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use GraphQL;
use DB;
use Cache;
use Exception;
use App\Library\Helper;
use GraphQL\Type\Definition\ObjectType;

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
            'nId' => 'required'
        ];
    }

    public function resolve($root, $args)
    {

        //查看是否是vip
//        $vipCardSign = NVipCardSign::select('VipCardTypeID', 'VipCardID')->where('VipCardCode', $args['vipNo'])->first();
//        $isVip = false;
//        $discount = 1;
//        if ($vipCardSign) {
//            $isVip = true;
//            $discount = Helper::getVipInfo($vipCardSign->VipCardTypeID);
//        }
//
//        $goods = $args['goods'];
//        $totalMoney = 0;     //总金额
//        $totalInMoney = 0;  //折扣后总金额
//        $totalDisMoney = 0;  //总折扣金额
//        foreach ($goods as $good) {
//            $price = PtypePrice::select('RetailPrice')->where('PtypeID', $good['typeId'])->first();
//            if (!$price) {
//                throw new Exception('商品不存在');
//            }
//            $money = ($price->RetailPrice) * $good['Qty'];
//            $inMoney = ($price->RetailPrice) * $good['Qty'] * $discount;
//            $disMoney = ($price->RetailPrice) * $good['Qty'] * (1 - $discount);
//            $totalMoney = +$money;
//            $totalInMoney += $inMoney;
//            $totalDisMoney += $disMoney;
//        }
        return ['msg' => '录单成功', 'code' => 200];

    }
}