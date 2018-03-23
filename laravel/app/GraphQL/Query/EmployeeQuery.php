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
        ];
    }

    public function resolve($root, $args)
    {

        $employee = new Employee();
        $query = Employee::query();

        if (isset($args['typeId'])) {
            $employee = $query->where('typeId', $args['typeId']);
        }

        if (isset($args['UserCode'])) {
            $employee = $query->where('UserCode', $args['UserCode']);
        }

        if (isset($args['Parid'])) {
            $employee = $query->where('Parid', $args['Parid']);
        }

        if (isset($args['leveal'])) {
            $employee = $query->where('leveal', $args['leveal']);
        }

        if (isset($args['FullName'])) {
            $employee = $query->where('FullName', 'like', '%' . $args['FullName'] . '%');
        }


        if (isset($args['offset'])) {
            $limit = 5;
            $offset = ($args['offset'] - 1) * $limit;
            $employee = $query->orderBy('typeid', 'desc')->offset($offset)->limit($limit)->get();
            return $employee;
        } else {
            return $employee->get();
        }

    }


}