<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/26
 * Time: 14:56
 */

namespace App\GraphQL\Type;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class NVipCardSignType extends GraphQLType
{

    protected $attributes = [
        'name' => 'nVipCardSign',
        'description' => 'nVipCardSign'
    ];

    public function fields()
    {
        return [
            'VipCardID' => [
                'type' => Type::int(),
                'description' => 'vip卡ID'
            ],
            'VipCardCode' => [
                'type' => Type::string(),
                'description' => 'vip编号'
            ],
            'VipCardTypeID' => [
                'type' => Type::string(),
                'description' => 'vip卡类型id'
            ],
            'Bname' => [
                'type' => Type::string(),
                'description' => '持卡人'
            ],
            'Bsex' => [
                'type' => Type::string(),
                'description' => '持卡人性别'
            ],
            'Btel' => [
                'type' => Type::string(),
                'description' => '持卡人电话'
            ],
            'Bbirthday' => [
                'type' => Type::string(),
                'description' => '持卡人生日'
            ],
            'CreateDate' => [
                'type' => Type::string(),
                'description' => '创建日期'
            ],
            'EndDate' => [
                'type' => Type::string(),
                'description' => '结束使用日期'
            ],
            'BeginMoney' => [
                'type' => Type::float(),
                'description' => '初始金额'
            ],
            'BeginCent' => [
                'type' => Type::float(),
                'description' => '初始积分'
            ],
            'totalMoney' => [
                'type' => Type::float(),
                'description' => '总金额'
            ],
            'totalCent' => [
                'type' => Type::float(),
                'description' => '总积分'
            ],
            'etypeid' => [
                'type' => Type::string(),
                'description' => '发卡人，关联eployee'
            ],
            'offset' => [
                'type' => Type::int(),
                'description' => '分页码数'
            ]
        ];
    }
}