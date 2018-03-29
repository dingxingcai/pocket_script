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

    public function authorize(array $args)
    {
        return !\Auth::guest();
    }

    protected $attributes = [
        'name' => 'ptype'
    ];

    public function type()
    {
//        return Type::listOf(GraphQL::type('ptype'));
        return GraphQL::paginate('ptype');
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
            'search' => ['name' => 'search', Type::string()],
            'limit' => ['name' => 'limit', Type::int()],
            'page' => ['name' => 'page', Type::int()],
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

        if (isset($args['search']) && !empty($args['search'])) {
            $query->where('FullName', 'like', '%' . $args['search'] . '%');
            $query->orWhere('EntryCode', 'like', '%' . $args['search'] . '%');
            $query->orWhere('UserCode', 'like', '%' . $args['search'] . '%');
            $query->orWhere('Standard', 'like', '%' . $args['search'] . '%');
        }

        return $query->orderBy('typeId', 'desc')->paginate($args['limit'], ['*'], 'page', $args['page']);


    }
}