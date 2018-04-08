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
 * 七天新增的会员数量
 * */

class VipType extends GraphQLType
{
    protected $inputObject = false;

    protected $attributes = [
        'name' => 'vip',
        'description' => 'vip'
    ];


    public function fields()
    {
        return [
            'date' => [
                'type' => Type::string(),
                'description' => '日期'
            ],
            'vipNums' => [
                'type' => Type::int(),
                'description' => '总会员数量'
            ],
        ];
    }


}