<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/12
 * Time: 15:41
 */

namespace App\Library;

use App\Employee;
use App\PosInfo;
use App\PosType;
use Cache;
use Exception;

class Helper
{

    //生成token
    public static function token($id)
    {
        $time = substr(microtime(false), 0, 5);
        return md5($time . $id);
    }


    //登录验证
    public static function checkLogin($token)
    {
        $user = Cache::get($token);
        if (empty($user)) {
            throw new Exception("请先登录");
        }

        return $user;
    }

    //获取posInfo信息
    public static function posInfo()
    {

        $user = \JWTAuth::parseToken()->authenticate();

        //查找用户的父id
        $parid = Employee::select('Parid')->where('typeId', $user->uid)->first();

        //查找posType
        $posType = PosType::where('etypeId', $parid->Parid)->first();

        //pos信息
        $posInfo = PosInfo::where('UserCode', $posType->posCode)->first();

        return $posInfo;

    }

}