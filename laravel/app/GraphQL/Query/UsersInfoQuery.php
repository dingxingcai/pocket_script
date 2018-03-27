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
use App\Library\Helper;

class UsersInfoQuery extends Query
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
            'token' => ['name' => 'token', Type::string()],

        ];
    }

    public function resolve($root, $args)
    {
        $user = Helper::checkLogin($args['token']);
        return User::where('uid',$user['uid'])->get();
    }


}