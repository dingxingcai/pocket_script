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

class GoodsStockType extends GraphQLType
{

    protected $attributes = [
        'name' => 'goodsStock',
        'description' => 'goodsStock'
    ];


    public function fields()
    {
        return [
            'PtypeId' => [
                'type' => Type::string(),
                'description' => '商品id'
            ],
            'KtypeId' => [
                'type' => Type::string(),
                'description' => '仓库id'
            ],
            'Qty' => [
                'type' => Type::int(),
                'description' => '数量'
            ],
            'Price' => [
                'type' => Type::float(),
                'description' => '价格'
            ],
            'Total' => [
                'type' => Type::float(),
                'description' => '总金额'

            ],
            'ProduceDate' => [
                'type' => Type::string(),
                'description' => '生产日期'
            ]

        ];
    }


}