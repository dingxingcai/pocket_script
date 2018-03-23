<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 16:57
 */

namespace App\GraphQL\Query;

use App\RetailBill;
use Rebing\GraphQL\Support\Query;
use GraphQL\Type\Definition\Type;
use GraphQL;

class RetailBillQuery extends Query
{
    protected $attributes = [
        'name' => 'retailBill',
        'description' => 'retailBill'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('retailBill'));
    }

    public function args()
    {
        return [
            'BillNumberId' => ['name' => 'BillNumberId', Type::string()],
            'PtypeId' => ['name' => 'PtypeId', Type::string()],
            'offset' => ['name' => 'offset',Type::int()]
        ];
    }

    public function resolve($root , $args){

        $query = RetailBill::query();
        if(isset($args['BillNumberId'])){
            $query-> where('BillNumberId',$args['BillNumberId']);
        }

        if(isset($args['PtypeId'])){
            $query->where('PtypeId',$args['PtypeId']);
        }

        $limit = 5;
        if(!isset($args['offset'])){
            $offset = 0;
        }else{
            $offset = ($args['offset'] -1 ) * $limit;
        }
        return $query->orderBy('BillNumberId','desc')->offset($offset)->limit($limit)->get();
    }

}