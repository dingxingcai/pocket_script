<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 15:26
 */

namespace App\GraphQL\Query;

use App\BillIndex;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use GraphQL;
use Rebing\GraphQL\Support\SelectFields;

class BillIndexQuery extends Query
{

    protected $attributes = [
        'name' => 'billIndexQuery'
    ];

    public function type()
    {
//        return Type::listOf(GraphQL::type('billIndex'));
        return GraphQL::paginate('billIndex');
    }

    public function args()
    {
        return [
            'BillNumberId' => ['name' => 'BillNumberId', Type::string()],
            'BillCode' => ['name' => 'BillCode', Type::string()],
            'BillType' => ['name' => 'BillType', Type::string()],
            'etypeid' => ['name' => 'etypeid', Type::string()],
            'ktypeid' => ['name' => 'ktypeid', Type::string()],
            'VipCardID' => ['name' => 'VipCardID', Type::string()],
            'btypeid' => ['name' => 'btypeid', Type::string()],
            'limit' => ['name' => 'limit', Type::int()],
            'page' => ['name' => 'page', Type::int()]
        ];
    }


    public function resolve($root, $args)
    {


        $query = BillIndex::where('BillType', 305);
        if (isset($args['BillNumberId'])) {
            $query->where('BillNumberId', $args['BillNumberId']);
        }
        if (isset($args['BillCode'])) {
            $query->where('BillCode', $args['BillCode']);
        }
        if (isset($args['BillType'])) {
            $query->where('BillType', $args['BillType']);
        }

        if (isset($args['etypeid'])) {
            $query->where('etypeid', $args['etypeid']);
        }
        if (isset($args['ktypeid'])) {
            $query->where('ktypeid', $args['ktypeid']);
        }
        if (isset($args['VipCardID'])) {
            $query->where('VipCardID', $args['VipCardID']);
        }

        return $query->orderBy('BillNumberId', 'desc')->paginate($args['limit'], ['*'], 'page', $args['page']);

//        $limit = 30;
//        if (!isset($args['offset'])) {
//            $offset = 0;
//        } else {
//            $offset = ($args['offset'] - 1) * $limit;
//        }
//
//        return $billIndex->orderBy('BillNumberId')->limit($limit)->offset($offset)->get();
    }
}