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

class UsersQuery extends Query
{

    protected $attributes = [
        'name' => 'user'
    ];

    public function type()
    {

        return Type::listOf(GraphQL::type('user'));
    }


    public function args()
    {
        return [
            'id' => ['name' => 'id', Type::int()],
            'uid' => ['name' => 'uid', Type::string()],
            'usercode' => ['name' => 'usercode', Type::string()],
            'name' => ['name' => 'name', Type::string()],
            'offset' => ['name' => 'offset', Type::int()],
            'password' => ['name' => 'password', Type::string()],
            'login' => ['name' => 'login', Type::string()],
        ];
    }

    public function resolve($root, $args)
    {
        print_r($args);exit;
        $query = User::query();
        $user = new User();
        if (isset($args['id'])) {
            $user = $query->where('id', $args['id']);
        }

        if (isset($args['uid'])) {
            $user = $query->where('uid', $args['uid']);
        }

        if (isset($args['usercode'])) {
            $user = $query->where('usercode', $args['usercode']);
        }

        if (isset($args['name'])) {
            $user = $query->where('name', 'like', '%' . $args['name'] . '%');
        }


        if (isset($args['offset'])) {
            $limit = 30;
            $offset = ($args['offset'] - 1) * $limit;
            $user = $query->orderBy('id', 'desc')->offset($offset)->limit($limit)->get();
            return $user;
        } else {
            return $user->get();
        }
    }


}