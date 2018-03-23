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

class EmployeeType extends GraphQLType
{

    protected $attributes = [
        'name' => 'employee',
        'description' => 'employee'
    ];


    public function fields()
    {
        return [
            'typeId' => [
                'type' => Type::string(),
                'description' => '店员id'
            ],
            'Parid' => [
                'type' => Type::string(),
                'description' => '店员父id'
            ],
            'UserCode' => [
                'type' => Type::string(),
                'description' => '店员编码'
            ],
            'leveal' => [
                'type' => Type::int(),
                'description' => '用户级别'
            ],
            'FullName' => [
                'type' => Type::string(),
                'description' => '店员名称'

            ],
            'Department' => [
                'type' => Type::string(),
                'description' => '部门名称'
            ],
            'Tel' => [
                'type' => Type::string(),
                'description' => '电话号码'
            ],
            'Sex' => [
                'type' => Type::string(),
                'description' => '性别 1男  0女'
            ],
            'Email' => [
                'type' => Type::string(),
                'description' => '邮箱'
            ],
            'offset' => [
                'type' => Type::int(),
                'description' => '页码数据'
            ],


        ];
    }


}