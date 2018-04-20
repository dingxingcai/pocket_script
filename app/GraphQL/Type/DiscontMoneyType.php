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
 * 计算折扣返回数据
 * */

class DiscontMoneyType extends GraphQLType
{
    protected $inputObject = false;


    protected $attributes = [
        'name' => 'discountMoney',
        'description' => '计算折扣返回的是数据'
    ];


    public function fields()
    {
        return [
            'totalMoney' => [
                'type' => Type::float(),
                'description' => '总的金额'
            ],
            'discountMoney' => [
                'type' => Type::float(),
                'description' => '折扣金额'
            ],
            'discount' => [
                'type' => Type::float(),
                'description' => '折扣率'
            ],
            'isVip' => [
                'type' => Type::boolean(),
                'description' => '是否是会员'
            ],
            'name' => [
                'type' => Type::string(),
                'description' => '会员名称'
            ]
        ];
    }


}