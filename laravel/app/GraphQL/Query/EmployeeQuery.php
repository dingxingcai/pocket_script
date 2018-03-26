<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 18:09
 */

namespace App\GraphQL\Query;

use App\Employee;
use App\User;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class EmployeeQuery extends Query
{

    protected $attributes = [
        'name' => 'employee'
    ];

    public function type()
    {

        return Type::listOf(GraphQL::type('employee'));
    }


    public function args()
    {
        return [
            'typeId' => ['name' => 'typeId', Type::string()],
            'UserCode' => ['name' => 'UserCode', Type::string()],
            'FullName' => ['name' => 'FullName', Type::string()],
            'leveal' => ['name' => 'leveal', Type::int()],
            'Parid' => ['name' => 'Parid', Type::string()],
            'offset' => ['name' => 'offset', Type::int()]
        ];
    }

    public function resolve($root, $args)
    {

        $query = Employee::query();

        if (isset($args['typeId'])) {
             $query->where('typeId', $args['typeId']);
        }

        if (isset($args['UserCode'])) {
             $query->where('UserCode', $args['UserCode']);
        }

        if (isset($args['Parid'])) {
             $query->where('Parid', $args['Parid']);
        }

        if (isset($args['leveal'])) {
            $query->where('leveal', $args['leveal']);
        }

        if (isset($args['FullName'])) {
            $query->where('FullName', 'like', '%' . $args['FullName'] . '%');
        }

        $limit = 30;
        if (!isset($args['offset'])) {
            $offset = 0;
        } else{
            $offset = ($args['offset'] - 1) * $limit;
        }

        return $employee = $query->orderBy('typeid', 'desc')->offset($offset)->limit($limit)->get();

    }


}