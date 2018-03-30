<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 17:32
 */

namespace App\GraphQL\Type;

use App\Post;
use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL;
use GraphQL\Type\Definition\InputType;

class TestType extends GraphQLType
{
    protected $inputObject = false;

    protected $attributes = [
        'name' => 'test',
        'description' => 'test'
    ];


    public function fields()
    {
        return [
            'money' => [
                'type' => Type::float(),
                'description' => 'money'
            ],
            'money3' => [
                'type' => Type::float(),
                'description' => 'money1'
            ],
            'user' => [
                'args' => [
                    'id' => [
                        'type' => Type::int(),
                        'description' => '类型'
                    ]
                ],
                'type' => Type::listOf(GraphQL::type('user')),
//                'type' => GraphQL::type('user'),
                'description' => 'user',
                'query' => function(array $aryay, $query){
                    return $query->where('user.id',$aryay['id']);
                }
            ],
            'tst' => [
                'type' => Type::int(),
                'description' => '',
                'privacy' => function (array $args) {
                    return $args['money'] == 0.01;
                }
            ]


        ];
    }

    public function resolveUserField($root, $args)
    {

        if (isset($args['id'])) {
            $user = User::find($args['id']);
            return [

                'id' => $args['id'],
                'name' => $user->usercode

            ];
        }

        return [

            'id' => 6,
            'name' => 6

        ];
    }

}