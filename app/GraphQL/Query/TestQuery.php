<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 18:09
 */

namespace App\GraphQL\Query;

use App\User;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use Cache;
use Exception;
use DB;
use App\Library\Helper;
use Log;
use Rebing\GraphQL\Support\SelectFields;
use GraphQL\Type\Definition\ResolveInfo;

class TestQuery extends Query
{

    protected $attributes = [
        'name' => 'test'
    ];

    public function type()
    {

//        return GraphQL::type('test');
        return Type::listOf(GraphQL::type('test'));
    }


    public function args()
    {
        return [
            'id' => ['name' => 'id', Type::int()],
            'money' => ['name' => 'money', Type::float()],
            'usercode' => ['name' => 'usercode', Type::string()],
            'name' => ['name' => 'name', Type::string()],
            'offset' => ['name' => 'offset', Type::int()],
            'password' => ['name' => 'password', Type::string()],
            'login' => ['name' => 'login', Type::string()],
        ];
    }

    public function resolve($root, $args ,SelectFields $selectFields , ResolveInfo $resolveInfo)
    {
        print_r($selectFields->getRelations());exit;
        return [
            ['money3' => '0.98899', 'tst' => 8],
            ['money3' => '0.8888', 'tst' => 9],
            ['money3' => '0.34782', 'tst' => 10],
//            'user' => [[
//                'id' => 2,
//                'name' => '666'
//            ], [
//                'id' => 3,
//                'name' => '777'
//            ]
//
//            ]
        ];
    }


}