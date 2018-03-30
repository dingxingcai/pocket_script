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

class AccountType extends GraphQLType
{
    protected $inputObject = false;


    protected $attributes = [
        'name' => 'account',
        'description' => '收款账户'
    ];


    public function fields()
    {
        return [
            'nId' => [
                'type' => Type::int(),
                'description' => '账号id'
            ],
            'UserCode' => [
                'type' => Type::string(),
                'description' => '账户编码'
            ],
            'FullName' => [
                'type' => Type::string(),
                'description' => '账户全名'
            ],

        ];
    }


}