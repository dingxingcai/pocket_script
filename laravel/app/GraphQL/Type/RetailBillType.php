<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 16:37
 */

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use GraphQL;


class RetailBillType extends GraphQLType
{
    protected $inputObject=true;

    protected $attributes = [
        'name' => 'retailBill',
        'description' => 'retailBill'
    ];

    public function fields()
    {
        return [
            'BillNumberId' => [
                'type' => Type::int(),
                'description' => '订单id，关联billindex'
            ],
            'PtypeId' => [
                'type' => Type::string(),
                'description' => '商品编号，关联pype'
            ],
            'Qty' => [
                'type' => Type::int(),
                'description' => '商品数量'
            ],
            'SalePrice' => [
                'type' => Type::float(),
                'description' => '零售价格'
            ],
            'discount' => [
                'type' => Type::float(),
                'description' => '折扣率'
            ],
            'DiscountPrice' => [
                'type' => Type::float(),
                'description' => '折扣后价格'
            ],
            'total' => [
                'type' => Type::float(),
                'description' => '折后总金额'
            ],
            'EtypeID' => [
                'type' => Type::string(),
                'description' => '录单职员id'
            ],
            'offset' => [
                'type' => Type::int(),
                'description' => '分页数据'
            ]
        ];
    }

}