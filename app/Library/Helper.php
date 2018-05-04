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


    //计算两个日期的天数
    public static function getDiffDate($startDay, $endDay)
    {
        if (empty($startDay) || empty($endDay)) {
            return 0;
        }
        $startdate = strtotime($startDay);
        $enddate = strtotime($endDay);
        $days = round(($enddate - $startdate) / 86400) + 1;
        return $days;
    }


    //计算百分数
    public static function getNum($num1, $num2)
    {
        $num = false;
        if (is_numeric($num1) && is_numeric($num2)) {
            $num = round(($num1 / $num2) * 100, 3);
            return $num;
        } else {
            return $num;
        }
    }

    //统计各个仓库的月目标
    public static function getMonthTarGet()
    {
        return [
            '0002200001' => [
                'money' => 1000000
            ],
            '0002300001' => [
                'money' => 500000
            ],
            '0002700001' => [
                'money' => 400000
            ],
            '0002600001' => [
                'money' => 700000
            ],
            '0002800001' => [
                'money' => 350000
            ],
            '0002400001' => [
                'money' => 250000
            ],
            '0003000001' => [
                'money' => 900000
            ],
            '0003100001' => [
                'money' => 450000
            ],
            '0003200001' => [
                'money' => 600000
            ],
            '0003400001' => [
                'money' => 1100000
            ],
            '0003700001' => [
                'money' => 350000
            ],
            '0004100001' => [
                'money' => 400000
            ],
            '0004300001' => [
                'money' => 200000
            ],
            '0004400001' => [
                'money' => 100000
            ],
            '0004800001' => [
                'money' => 200000
            ],
            '0005100001' => [   //北京M7店
                'money' => 150000
            ]
        ];
    }


}