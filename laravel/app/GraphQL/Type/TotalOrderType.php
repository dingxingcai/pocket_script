<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 17:32
 */

namespace App\GraphQL\Type;

use App\Post;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL;
use GraphQL\Type\Definition\InputType;


/*
 * 七天总的订单
 * */

class TotalOrderType extends GraphQLType
{
    protected $inputObject = false;

    protected $attributes = [
        'name' => 'totalOrder',
        'description' => 'totalOrder'
    ];


    public function fields()
    {
        return [
            'date' => [
                'type' => Type::string(),
                'description' => '日期'
            ],
            'totalOrders' => [
                'type' => Type::int(),
                'description' => '总订单数量'
            ],
            'vipOrders' => [
                'type' => Type::int(),
                'description' => '会员订单数量'
            ],
            'notVipOrders' => [
                'type' => Type::int(),
                'description' => '非会员订单数量'
            ],
        ];
    }


}