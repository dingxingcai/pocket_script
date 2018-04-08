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
use Exception;

/*
 * 修改密码
 * */

class ChangePwdMutation extends Mutation
{


    public function authorize(array $args)
    {
        return !\Auth::guest();
    }

    protected $attributies = [
        'name' => 'changePwd'
    ];

    public function type()
    {
        return GraphQL::type('return');
    }

    public function rules(array $args = [])
    {
        return [
            'oldPwd' => 'required|string|min:6',
            'newPwd1' => 'required|string|min:6',
            'newPwd2' => 'required|string|min:6',
        ];
    }

    public function args()
    {
        return [
            'oldPwd' => ['name' => 'oldPwd', 'type' => Type::string()],
            'newPwd1' => ['name' => 'newPwd1', 'type' => Type::string()],
            'newPwd2' => ['name' => 'newPwd2', 'type' => Type::string()],
        ];
    }

    public function resolve($root, $args)
    {
        $info = \JWTAuth::parseToken()->authenticate();

        $user = User::find($info->id);

        if (!$user) {
            throw new Exception('用户不存在');
        }

        if ($args['newPwd1'] !== $args['newPwd2']) {
            throw new Exception('两次输入的密码不一致，请重新输入');
        }

        $oldPwd = md5($user->uid . "uid" . $args['oldPwd']);
        if ($oldPwd != $user->password) {
            throw new Exception("原始密码输入错误，请重新输入");
        }

        $user->password = md5($user->uid . 'uid' . $args['newPwd1']);

        $user->save();

        return [
            'msg' => '修改成功',
            'code' => 200
        ];
    }

}