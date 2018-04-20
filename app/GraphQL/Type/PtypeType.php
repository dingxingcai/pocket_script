<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 16:37
 */

namespace App\GraphQL\Type;

use App\GoodsStock;
use App\Library\Helper;
use App\PtypePrice;
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
            'Qty' => [
                'type' => Type::int(),
                'description' => '商品的库存'
            ],
            'RetailPrice' => [
                'type' => Type::float(),
                'description' => '商品的价格'
            ]
        ];
    }
}