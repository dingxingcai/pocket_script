<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/26
 * Time: 15:14
 */

namespace App\GraphQL\Query;

use App\NVipCardSign;
use Rebing\GraphQL\Support\Query;
use GraphQL\Type\Definition\Type;
use GraphQL;

class NVipCardSignQuery extends Query
{

    protected $attributes = [
        'name' => 'NVipCardSign'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('nVipCardSign'));
    }

    public function args()
    {
        return [
            'VipCardID' => ['name' => 'VipCardID', Type::int()],
            'VipCardCode' => ['name' => 'VipCardCode', Type::string()],
            'VipCardTypeID' => ['name' => 'VipCardTypeID', Type::string()],
            'Bname' => ['name' => 'Bname', Type::string()],
            'etypeid' => ['name' => 'etypeid', Type::string()],
            'offset' => ['name' => 'offset', Type::int()]
        ];
    }

    public function resolve($root, $args)
    {

        $query = NVipCardSign::query();
        if (isset($args['VipCardID'])) {
            $query->where('VipCardID', $args['VipCardID']);
        }
        if (isset($args['VipCardCode'])) {
            $query->where('VipCardCode', $args['VipCardCode']);
        }
        if (isset($args['VipCardTypeID'])) {
            $query->where('VipCardTypeID', $args['VipCardTypeID']);
        }
        if (isset($agrs['Bname'])) {
            $query->where('Bname', $args['Bname']);
        }

        $limit = 50;
        if (isset($args['offset'])) {
            $offset = ($args['offset'] - 1) * $limit;
        } else {
            $offset = 0;
        }

        return $query->orderBy('VipCardID', 'desc')->limit($limit)->offset($offset)->get();


    }
}