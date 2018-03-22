<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/21
 * Time: 09:55
 */

namespace App\GraphQL\Mutation;

use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use GraphQL;


class updateUserMutation extends Mutation
{

    protected  $attributies = [
        'name' => 'updateUser'
    ];

    public  function type()
    {
        return GraphQL::type('user');
    }

    public function rules(array $args = []){
        return [
            'id' => 'required',
            'usercode'    => 'required|string|min:2',
            'name'     => 'required|string|min:2',
            'password' => 'required|string|min:6',
        ];
    }

    public function args()
    {
        return  [
            'id'        => ['name' => 'id' ,        'type' => Type::int()],
            'uid'       => ['name' => 'uid',        'type' => Type::string()],
            'usercode'  => ['name' => 'usercode' ,  'type' => Type::string()],
            'telephone' => ['name' => 'telephone',  'type' => Type::string()],
            'password'  => ['name' => 'password' ,  'type' => Type::string()],
            'name'      => ['name' => 'name',       'type' => Type::string()],
        ];
    }

    public function resolve($root , $args){
            $user = User::find($args['id']);
            if(!$user){
                return null;
            }

            if(isset($args['password'])){
                $args['password'] = md5($args['password']);
            }

            $user->update($args);
            return $user;
    }

}