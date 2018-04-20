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
use GraphQL\Type\Definition\InputType;


/*
 * 七天新增的会员数量
 * */

class TitleType extends GraphQLType
{
    protected $inputObject = false;

    protected $attributes = [
        'name' => 'title',
    ];


    public function fields()
    {
        return [
            'name' => [
                'type' => Type::string(),
                'description' => ''
            ],
//            'item2' => [
//                'type' => Type::string(),
//                'description' => ''
//            ],
//            'item3' => [
//                'type' => Type::string(),
//                'description' => ''
//            ],
//            'item4' => [
//                'type' => Type::string(),
//                'description' => ''
//            ],
//            'item5' => [
//                'type' => Type::string(),
//                'description' => ''
//            ],
//            'item6' => [
//                'type' => Type::string(),
//                'description' => ''
//            ],

        ];
    }


}