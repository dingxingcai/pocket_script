<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 14:33
 */

namespace App\GraphQL\Type;

use App\NVipCardSign;
use App\RetailBill;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL;

class BillIndexType extends GraphQLType
{
    protected $attributes = [
        'name' => 'billindex',
        'description' => 'billIndex'
    ];

    public function fields()
    {
        return [
            'BillNumberId' => [
                'type' => Type::int(),
                'description' => '订单id'
            ],
            'BillDate' => [
                'type' => Type::string(),
                'description' => '订单日期'
            ],
            'BillCode' => [
                'type' => Type::string(),
                'description' => '订单编号'
            ],
            'BillType' => [
                'type' => Type::int(),
                'description' => '订单类别  305-零售单'
            ],
            'ptypeid' => [
                'type' => Type::string(),
                'description' => '商品编码'
            ],
            'btypeid' => [
                'type' => Type::string(),
                'description' => '结算单位'
            ],
            'etypeid' => [
                'type' => Type::string(),
                'description' => '职员id'
            ],
            'ktypeid' => [
                'type' => Type::string(),
                'description' => '仓库id'
            ],
            'totalmoney' => [
                'type' => Type::float(),
                'description' => '订单金额'
            ],
            'totalinmoney' => [
                'type' => Type::float(),
                'desrription' => '订单实收金额'
            ],
            'totalqty' => [
                'type' => Type::int(),
                'description' => '订单商品数量'
            ],

            'preferencemoney' => [
                'type' => Type::float(),
                'description' => '优惠金额，抹零金额'
            ],
            'DTypeId' => [
                'type' => Type::string(),
                'description' => ''
            ],
            'VipCardID' => [
                'type' => Type::string(),
                'description' => '会员卡号,为 -1是非会员'
            ],
            'JF' => [
                'type' => Type::int(),
                'description' => '订单积分'
            ],
            'jsStyle' => [
                'type' => Type::int(),
                'description' => '结算方式:0 - 无，1 - 按单结算，2 - 按商品结算'
            ],
            'jsState' => [
                'type' => Type::int(),
                'description' => '结算状态: 0 - 未结算，- 未完成结算，- 结算完成'
            ],
            'checkTime' => [
                'type' => Type::string(),
                'description' => '订单确认时间'
            ],
            'posttime' => [
                'type' => Type::string(),
                'description' => '订单时间'
            ],
            'BillTime' => [
                'type' => Type::string(),
                'description' => '订单时间 时分秒'
            ],
            'Discount' => [
                'type' => Type::float(),
                'description' => '折扣率'
            ],
            'token' => [
                'type' => Type::string(),
                'description' => 'token'
            ],
            'page' => [
                'type' => Type::int(),
                'description' => '页码数'
            ],
            'limit' => [
                'type' => Type::int(),
                'description' => '分页限制'
            ],
            'retailBill' => [    //关联retailBill
                'type' => Type::listOf(GraphQL::type('retailBill')),
                'description' => 'RetailBill',
            ],
            'nVipCardSign' => [    //关联 nVipCardSign
                'type' => Type::listOf(GraphQL::type('nVipCardSign')),
                'description' => 'nVipCardSign'
            ]


        ];
    }

    public function resolveRetailBillField($root, $args)
    {
        if (isset($args['PtypeId'])) {
            return RetailBill::where('PtypeId', $args['PtypeId'])->get();
        }
        return RetailBill::where('BillNumberId', $root->BillNumberId)->get();
    }

    public function resolvenVipCardSignField($root, $args)
    {

        if ($root->VipCardID !== -1) {
            return NVipCardSign::where('vipCardID', $root->VipCardID)->get();
        }
        return null;
    }


}