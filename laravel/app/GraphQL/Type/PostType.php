<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/21
 * Time: 16:23
 */

namespace App\GraphQL\Type;

use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL;


class PostType extends GraphQLType
{
    protected $attributes = [
        'name' => 'post',
        'description' => 'description post'
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'id'
            ],
            'email' => [
                'type' => Type::string(),
                'description' => 'email'
            ],
            'uid' => [
                'type' => Type::string(),
                'description' => 'uid'
            ],
//            'user' => [
//                'args' => [
//                    'id' => Type::int(),
//                    'description' => 'user id'
//                ],
//                'type' => Type::listOf(GraphQl::type('user')),
//                'description' => 'description',
//            ],
        ];
    }

    public function resolveUserField($root, $args)
    {
        if (isset($args['id'])) {
            return User::where('id', $args['id'])->get();
        }

        return User::get();
    }

}