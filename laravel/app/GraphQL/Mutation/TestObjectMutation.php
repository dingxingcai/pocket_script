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
use DB;
use Cache;
use Exception;
use App\Library\Helper;
use GraphQL\Type\Definition\ObjectType;

class TestObjectMutation extends Mutation
{

    protected $attributes = [
        'name' => 'testObject'
    ];


    public function type()
    {
        return GraphQL::type('user');
    }

    public function args()
    {
        return [
            'goods' => ['name' => 'goods', 'type' => Type::listOf(GraphQL::type('retailBill'))],
//            'vip' => ['name' => 'vip', 'type' => ],
            'vip' => ['name' => 'vip', 'type' => GraphQL::type('user')],
        ];
    }

    public function resolve($root, $args)
    {
//        $goods = $args['goods'];
//        $vip = $args['vip'];

        print_r($args);
        exit;
    }
}