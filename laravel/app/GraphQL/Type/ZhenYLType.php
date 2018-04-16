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
 * 郑伊露需要的数据
 * */

class ZhenYLType extends GraphQLType
{
    protected $inputObject = false;

    protected $attributes = [
        'name' => 'zhenYL',
    ];


    public function fields()
    {
        return [
            'stock' => [
                'type' => Type::string(),
                'description' => '仓库名称'
            ],
            'dayMoney' => [
                'type' => Type::float(),
                'description' => '当天销售额'
            ],
            'totalMoney' => [
                'type' => Type::float(),
                'description' => '本月总销售额'
            ],
            'target' => [
                'type' => Type::float(),
                'description' => '当月目标'
            ],
            'finishedCount' => [
                'type' => Type::string(),
                'description' => '完成率'
            ],
            'diff' => [
                'type' => Type::float(),
                'description' => '速度完成对比差值'
            ],
            'title' => [
                'type' => Type::string(),
                'description' => '标题'
            ]
        ];
    }


}