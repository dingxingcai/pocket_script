<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 17:32
 */

namespace App\GraphQL\Type;

use App\Post;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL;

class UserType extends GraphQLType
{

    protected $attributes = [
        'name' => 'user',
        'description' => 'test A User'
    ];


    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'primary key'
            ],
            'uid' => [
                'type' => Type::string(),
                'description' => 'user id'
            ],
            'usercode' => [
                'type' => Type::string(),
                'description' => '用户编码'
            ],
            'name' => [
                'type' => Type::string(),
                'description' => '用户姓名'
            ],
            'loginat' => [
                'type' => Type::string(),
                'description' => '登录时间'

            ],
            'password' => [
                'type' => Type::string(),
                'description' => 'password'
            ],
            'telephone' => [
                'type' => Type::string(),
                'description' => 'telephone'
            ],
            'offset' => [
                'type' => Type::int(),
                'description' => '分页数量'
            ],
            'post' => [
                'args' => [
                    'id' => [
                        'type' => Type::int(),
                        'description' => 'description'
                    ],
                    'email' => [
                        'type' => Type::string(),
                        'description' => 'email'
                    ]
                ],
                'type' => Type::listOf(GraphQL::type('post')),
                'description' => 'description'
            ],

        ];
    }

    public function resolvePostField($root, $args)
    {

        if (isset($args['id'])) {
            return Post::where('id', $args['id'])->get();
        }

//        return $root->posts;

        return Post::get();
    }


}