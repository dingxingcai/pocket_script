<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 15:26
 */

namespace App\GraphQL\Query;

use App\BillIndex;
use App\Ptype;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use GraphQL;

class PtypeQuery extends Query
{

    protected $attributes = [
        'name' => 'ptypeQuery'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('ptype'));
    }

    public function args()
    {
        return [
            'typeId' => ['name' => 'typeId', Type::string()],
            'Parid' => ['name' => 'Parid', Type::string()],
            'leveal' => ['name' => 'leveal', Type::int()],
            'UserCode' => ['name' => 'etypeid', Type::string()],
            'FullName' => ['name' => 'FullName', Type::string()],
            'EntryCode' => ['name' => 'EntryCode', Type::string()],
            'offset' => ['name' => 'offset', Type::int()]
        ];
    }


    public function resolve($root, $args)
    {

        $query = Ptype::query();
        if (isset($args['typeId'])) {
            $query->where('typeId', $args['typeId']);
        }
        if (isset($args['Parid'])) {
            $query->where('Parid', $args['Parid']);
        }
        if (isset($args['leveal'])) {
            $query->where('leveal', $args['leveal']);
        }

        if (isset($args['UserCode'])) {
            $query->where('UserCode', $args['UserCode']);
        }
        if (isset($args['FullName'])) {
            $query->where('FullName', 'like', '%' . $args['FullName'] . '%');
        }
        if (isset($args['EntryCode'])) {
            $query->where('EntryCode', $args['EntryCode']);
        }

        $limit = 30;
        if (!isset($args['offset'])) {
            $offset = 0;
        } else {
            $offset = ($args['offset'] - 1) * $limit;
        }

        return $query->orderBy('typeId', 'desc')->limit($limit)->offset($offset)->get();
    }
}