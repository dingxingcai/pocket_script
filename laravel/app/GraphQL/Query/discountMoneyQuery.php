<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 18:09
 */

namespace App\GraphQL\Query;

use App\AcItems;
use App\NVipCardSign;
use App\PtypePrice;
use App\User;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use App\Library\Helper;
use Exception;


class discountMoneyQuery extends Query
{

    protected $attributes = [
        'name' => 'discountMoney'
    ];

    public function type()
    {

        return GraphQL::type('discountMoney');
    }


    public function args()
    {
        return [
            'goods' => ['name' => 'goods', Type::listOf(GraphQL::type('goodsQty'))],
            'vipNo' => ['name' => 'vipNo', Type::string()],
        ];
    }

    public function resolve($root, $args)
    {

        //查看是否是vip
        $vipCardSign = NVipCardSign::select('VipCardTypeID')->where('VipCardCode', $args['vipNo'])->first();
        $isVip = false;
        $name = "非会员";
        $discount = 1;
        if ($vipCardSign) {
            $isVip = true;
            $discount = Helper::getVipCount($vipCardSign->VipCardTypeID);
            $name = Helper::getVipName($vipCardSign->VipCardTypeID);
        }

        $goods = $args['goods'];
        $totalMoney = 0;
        $totalDisMoney = 0;
        foreach ($goods as $good) {
            $price = PtypePrice::select('RetailPrice')->where('PtypeID', $good['typeId'])->first();
            if (!$price) {
                throw new Exception('商品不存在');
            }
            $money = ($price->RetailPrice) * $good['Qty'] * $discount;
            $disMoney = ($price->RetailPrice) * $good['Qty'] * (1 - $discount);
            $totalMoney += $money;
            $totalDisMoney += $disMoney;
        }

        return [
            'totalMoney' => round($totalMoney, 2),
            'discountMoney' => round($totalDisMoney, 2),
            'isVip' => $isVip,
            'discount' => $discount,
            'name' => $name
        ];


    }


}