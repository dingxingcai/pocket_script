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

    //获取会员折扣
    public static function getVipCount($type)
    {
        $account = [
            1 => 0.99,
            2 => 0.98,
            3 => 0.95,
            4 => 0.90,
            0 => 0.88
        ];

        return $account[$type];
    }

    //获取会员折扣
    public static function getVipName($type)
    {
        $account = [
            1 => "蓝口袋",
            2 => "青口袋",
            3 => "银口袋",
            4 => "金口袋",
            0 => "黑口袋"
        ];

        return $account[$type];
    }

    public static function getNum($num1, $num2)
    {
        $num = false;
        if (is_numeric($num1) && is_numeric($num2)) {
            $num = round(($num1 / $num2) * 100,3) . "%";
            return $num;
        } else {
            return $num;
        }
    }


}