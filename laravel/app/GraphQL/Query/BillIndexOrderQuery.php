<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 15:26
 */

namespace App\GraphQL\Query;

use App\BillIndex;
use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use GraphQL;
use Rebing\GraphQL\Support\SelectFields;

class BillIndexOrderQuery extends Query
{
    public function authorize(array $args)
    {
        return !\Auth::guest();
    }

    protected $attributes = [
        'name' => 'billIndexOrderQuery'
    ];

    public function type()
    {
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

        /** @var \App\User $user */
        $user = \JWTAuth::parseToken()->authenticate();
//        $user = User::find(44);
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

        $query->whereHas('retailBills', function ($q) use ($user) {
            $q->where('ETypeID', '=', $user->uid);
        });

        return $query->orderBy('BillNumberId', 'desc')->paginate($args['limit'], ['*'], 'page', $args['page']);

    }
}