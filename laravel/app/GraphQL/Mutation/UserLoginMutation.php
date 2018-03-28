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

class UserLoginMutation extends Mutation
{

    protected $attributes = [
        'name' => 'userLogin'
    ];


    public function type()
    {
        return GraphQL::type('user');
    }

    public function rules(array $args = [])
    {
        return [
            'name' => 'required|string|min:2',
            'password' => 'required|string|min:6',
        ];
    }

    public function args()
    {
        return [
            'password' => ['name' => 'password', 'type' => Type::string()],
            'name' => ['name' => 'name', 'type' => Type::string()],
        ];
    }

    public function resolve($root, $args)
    {


        $name = $args['name'];
        $password = $args['password'];

        $user = User::where('usercode', $name)->first();

        //存在users表里面
        if ($user) {

            //检查账号是否被删除
            $employee = DB::connection('sqlsrv')->table('employee')->select('deleted')->where('typeId', $user->uid)->first();

            if ($employee) {
                if ($employee->deleted === 1) {
                    throw  new Exception('对不起，您的账号不存在或已经被删除');
                }
            } else {
                throw  new Exception('对不起，您的账号不存在或已经被删除');
            }


            $pd = md5($user->uid . 'uid' . $password);
            if ($user->password !== $pd) {
                throw  new Exception('用户名或密码错误');
            }

            //登录成功，修改登录时间
            $user->loginat = date('Y-m-d H:i:s', time());
            $user->save();

            //将用户的信息存入cache中,默认时间是24小时
//            $token = Helper::token($user->uid);
//            $data = [
//                'usercode' => $name,
//                'uid' => $user->uid,
//                'username' => $user->name,
//            ];
//            Cache::add($token, $data, 1440);

            $token = \JWTAuth::fromUser($user);


        } else { //表示没有存在users表里面的
            $employee = DB::connection('sqlsrv')->table('employee')->where('UserCode', $name)->first();
            if ($employee) {
                if ($employee->deleted === 1) {
                    throw  new Exception('对不起，您的账号不存在或已经被删除');
                }

                //将用户的信息存入users
                $user = new User();
                $user->uid = $employee->typeId;
                $user->usercode = $name;
                $user->password = md5($employee->typeId . 'uid' . '123456');
                $user->loginat = date('Y-m-d H:i:s', time());
                $user->telephone = '';
                $user->name = $employee->FullName;
                $user->save();


//                //相同的，存入cache中,默认时间是24小时
//                $token = Helper::token($user->uid);
//                $data = [
//                    'usercode' => $name,
//                    'uid' => $user->uid,
//                    'username' => $user->name,
//                ];
//                Cache::add($token, $data, 1440);
                $token = \JWTAuth::fromUser($user);

            } else {
                throw  new Exception('对不起，您的账号不存在或已经被删除');
            }
        }

        $user->token = $token;
//        print_r($user);exit;
//        print_r($token);exit;
        return $user;
    }
}