<?php

namespace App\Http\Controllers;

use App\Employee;
use App\User;
use Illuminate\Http\Request;
use DB;
use Cache;
use App\Library\Helper;
use Exception;
use Illuminate\Support\Facades\Response;
use App\Library\Curl;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    //用户登录
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required'
        ]);


        $name = trim($request->input('name'));
        $password = trim($request->input('password'));

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
            $token = Helper::token($user->uid);
            $data = [
                'name' => $name,
                'uid' => $user->uid
            ];
            Cache::add($token, $data, 1440);


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


                //相同的，存入cache中,默认时间是24小时
                $token = Helper::token($user->uid);
                $data = [
                    'name' => $name,
                    'uid' => $user->uid
                ];
                Cache::add($token, $data, 1440);


            } else {
                throw  new Exception('对不起，您的账号不存在或已经被删除');
            }
        }

        return response()->json(['token' => $token]);

    }

    //用户修改密码
    public function changePwd(Request $request)
    {
        $request->validate([
            'oldPwd' => 'required',
            'newPwd1' => 'required',
            'newPwd2' => 'required',
            'token' => 'required'
        ]);

        $oldPwd = trim($request->input('oldPwd'));
        $newPwd1 = trim($request->input('newPwd1'));
        $newPwd2 = trim($request->input('newPwd2'));
        if ($newPwd1 !== $newPwd2) {
            throw new Exception('两次输入的密码不一致，请重新输入');
        }
        $token = $request->input('token');

        $data = Cache::get($token);

        $user = User::where('usercode', $data['name'])->first();
        if ($user) {

            //校验老密码
            $pd = md5($user->uid . 'uid' . $oldPwd);
            if ($pd !== $user->password) {
                throw new Exception('原密码输入错误');
            }

            $user->password = md5($user->uid . 'uid' . $newPwd1);
            $user->save();

        } else {
            throw new Exception('用户名不存在');
        }

        return response()->json();

    }

    public function getInfo(Request $request)
    {
        $url1 = 'https://api.cloudconvert.com/process';
        $param1 = [
            'apikey' => config('app.apikey'),
            'inputformat' => 'website',
            'outputformat' => 'jpg'
        ];
        $result1 = Curl::curl($url1, $param1, true, true);
        if ($result1 === false || !isset($result1['url'])) {
            throw new Exception('获取URL错误');
        }

        $url2 = $result1['url'];
        $param2 = [
            'wait' => true,
            'input' => 'url',
            'file' => 'http://md.sylicod.com/chart/#/?code=3',
            'filename' => 'test.website',
            'outputformat' => 'jpg'
        ];

        $result2 = Curl::curl('https:' . $url2, $param2, true, true);
        if ($result2 === false || !isset($result2['output'])) {
            throw new Exception('获取图片地址错误');
        }
        $url3 = $result2['output'];
        $imageUrl = 'https:' . $url3['url'];
        $ext = file_get_contents($imageUrl);
        $fileName = date('YmdHis', time()).mt_rand(1000,9999) . '.jpg';
        Storage::put("market/{$fileName}", $ext);
        $url = Storage::url("market/{$fileName}");
        if (empty($url)) {
            throw new Exception('获取的阿里云图片地址为空');
        }
        $dingdingUrl = config('app.dingdingUrl');
        $dingdingParam = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => '一周门店总单数',
                'text' => "![screenshot]({$url})"
            ],
            'at' => [
                'atMobiles' => [''],
                'isAtAll' => false,
            ]


        ];

        $result3 = Curl::curl($dingdingUrl, json_encode($dingdingParam), true, true, true);

        if ($result3['errcode'] != 0) {
            throw new Exception('发送钉钉消息失败');
        }

        return true;

    }

}
