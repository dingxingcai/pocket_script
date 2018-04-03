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

/*
 * 正常提示返回信息
 * */

class ReturnStringType extends GraphQLType
{
    protected $inputObject = false;

    protected $attributes = [
        'name' => 'returnString',
        'description' => 'test'
    ];


    public function fields()
    {
        return [

            'msg' => [
                'type' => Type::string(),
                'description' => '提示信息'
            ],
            'code' => [
                'type' => Type::int(),
                'description' => '状态码'
            ]


        ];
    }


}