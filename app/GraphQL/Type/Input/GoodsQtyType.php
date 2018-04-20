<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 17:32
 */

namespace App\GraphQL\Type\Input;

use App\Post;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL;
use GraphQL\Type\Definition\InputType;

/*
 * 下单时输入的商品信息
 * */

class GoodsQtyType extends GraphQLType
{
    protected $inputObject = true;


    protected $attributes = [
        'name' => 'goodsQty',
        'description' => '传入的商品的id和数量'
    ];


    public function fields()
    {
        return [
            'typeId' => [
                'type' => Type::string(),
                'description' => '商品id'
            ],
            'Qty' => [
                'type' => Type::int(),
                'description' => '商品数量'
            ],
            'FullName' => [
                'type' => Type::string(),
                'description' => '商品名称'
            ]
        ];
    }


}