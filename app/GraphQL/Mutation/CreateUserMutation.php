<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 19:06
 */

namespace App\GraphQL\Mutation;

use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use GraphQL;

class CreateUserMutation extends Mutation
{

    protected $attributes = [
        'name' => 'createUser'
    ];


    public function type()
    {
        return GraphQL::type('user');
    }

    public function rules(array $args = [])
    {
        return [
            'usercode' => 'required|string|min:2',
            'name' => 'required|string|min:2',
            'password' => 'required|string|min:6',
        ];
    }

    public function args()
    {
        return [
            'uid' => ['name' => 'uid', 'type' => Type::string()],
            'usercode' => ['name' => 'usercode', 'type' => Type::string()],
            'telephone' => ['name' => 'telephone', 'type' => Type::string()],
            'password' => ['name' => 'password', 'type' => Type::string()],
            'name' => ['name' => 'name', 'type' => Type::string()],
        ];
    }

    public function resolve($root, $args)
    {
//            $user = new User();
//            $user->uid = $args['uid'];
//            $user->usercode = $args['usercode'];
//            $user->telephone = $args['telephone'];
//            $user->password = $args['password'];
//            $user->name = $args['name'];
//            $user->save();
        return User::create($args);
    }
}