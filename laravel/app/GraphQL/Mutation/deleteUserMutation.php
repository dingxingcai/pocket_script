<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/21
 * Time: 10:13
 */

namespace App\GraphQL\Mutation;

use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use GraphQL;


class deleteUserMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deleteUser'
    ];

    public function type()
    {
        return GraphQL::type('user');
    }

    public function rules(array $args = [])
    {
        return [
//            'id'    => 'required|int',
            'uid' => 'string'
        ];
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()],
            'uid' => ['name' => 'uid', 'type' => Type::string()],
        ];
    }

    public function resolve($boot, $args)
    {
        if (isset($args['id'])) {
            $user = User::find($args['id']);
            $user->delete();
            return $user;
        }

        if (isset($args['uid'])) {
            $user = User::select('id', 'name')->where('uid', $args['uid'])->first();
            $user->delete();
            return $user;
        }
    }
}