<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 16:37
 */

namespace App\GraphQL\Type;

use App\GoodsStock;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use GraphQL;


class PtypeType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ptype',
        'description' => 'ptype'
    ];

    public function fields()
    {
        return [
            'typeId' => [
                'type' => Type::string(),
                'description' => '商品id'
            ],
            'Parid' => [
                'type' => Type::string(),
                'description' => '父级id'
            ],
            'leveal' => [
                'type' => Type::int(),
                'description' => '级别'
            ],
            'UserCode' => [
                'type' => Type::string(),
                'description' => '商品编码'
            ],
            'FullName' => [
                'type' => Type::string(),
                'description' => '名称'
            ],
            'Standard' => [
                'type' => Type::string(),
                'description' => '规格'
            ],
            'Type' => [
                'type' => Type::string(),
                'description' => '规格'
            ],
            'Area' => [
                'type' => Type::string(),
                'description' => '产地'
            ],
            'EntryCode' => [
                'type' => Type::string(),
                'description' => '条码'
            ],
            'CreateDate' => [
                'type' => Type::string(),
                'description' => '创建日期'
            ],
            'pid' => [
                'type' => Type::int(),
                'description' => '图片id'
            ],
            'offset' => [
                'type' => Type::int(),
                'description' => '分页码数'
            ],
            'goodsStock' => [
                'type' => Type::listOf(GraphQL::type('goodsStock')),
                'description' => '关联商品库存'
            ]
        ];
    }

    public function resolveGoodsStockField($root, $args)
    {
        return GoodsStock::where('PtypeId', $root->typeId)->where('KtypeId', '0002200004')->get();

    }

}