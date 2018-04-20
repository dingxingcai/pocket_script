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
 * 品牌销售统计
 * */

class SaleType extends GraphQLType
{
    protected $inputObject = false;

    protected $attributes = [
        'name' => 'sale',
        'description' => 'sale'
    ];


    public function fields()
    {
        return [
            'date' => [
                'type' => Type::string(),
                'description' => '日期'
            ],
            'money' => [
                'type' => Type::float(),
                'description' => '音频类销售额'
            ],
            'totalMoney' => [
                'type' => Type::float(),
                'description' => '当天总计销售额'
            ],
            'count' => [
                'type' => Type::float(),
                'description' => '所占百分比'
            ]
        ];
    }


}