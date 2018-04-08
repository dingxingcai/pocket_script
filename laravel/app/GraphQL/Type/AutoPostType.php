<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 17:32
 */

namespace App\GraphQL\Type;

use App\Post;
use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL;
use GraphQL\Type\Definition\InputType;

/*
 * 正常提示返回信息
 * */

class AutoPostType extends GraphQLType
{
    protected $inputObject = false;

    protected $attributes = [
        'name' => 'autoPost',
        'description' => 'autoPost'
    ];


    public function fields()
    {
        return [

            'vips' => [
                'type' => Type::listOf(GraphQL::type('vip')),
                'description' => '会员信息'
            ],
            'dayOrder' => [
                'type' => Type::listOf(GraphQL::type('dayOrder')),
                'description' => '当天订单'
            ],
            'totalOrder' => [
                'type' => Type::listOf(GraphQL::type('totalOrder')),
                'description' => '七天订单'
            ]


        ];
    }


}