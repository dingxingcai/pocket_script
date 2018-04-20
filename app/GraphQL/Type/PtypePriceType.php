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


class PtypePriceType extends GraphQLType
{
    protected $attributes = [
        'name' => 'pTypePrice',
        'description' => 'pTypePrice'
    ];

    public function fields()
    {
        return [
            'PTypeID' => [
                'type' => Type::string(),
                'description' => '商品id'
            ],
            'PreBuyPrice1' => [
                'type' => Type::float(),
                'description' => '预设进价1'
            ],
            'PreSalePrice1' => [
                'type' => Type::float(),
                'description' => '预设售价1'
            ],
            'RetailPrice' => [
                'type' => Type::float(),
                'description' => '零售价'
            ],
            'TopSalePrice' => [
                'type' => Type::float(),
                'description' => '最高价'
            ],
            'LowSalePrice' => [
                'type' => Type::float(),
                'description' => '最低价'
            ],
            'ReferPrice' => [
                'type' => Type::string(),
                'description' => '参考成本价'
            ],
            'page' => [
                'type' => Type::int(),
                'description' => '页码数'
            ],
            'limit' => [
                'type' => Type::int(),
                'description' => '分页大小'
            ],
        ];
    }


}