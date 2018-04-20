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

class BrandType extends GraphQLType
{
    protected $inputObject = false;

    protected $attributes = [
        'name' => 'brand',
        'description' => 'brand'
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
                'description' => '当天销售额'
            ],
            'name' => [
                'type' => Type::string(),
                'description' => '分类名称'
            ],
            'count' => [
                'type' => Type::float(),
                'description' => '所占百分比'
            ]
        ];
    }


}