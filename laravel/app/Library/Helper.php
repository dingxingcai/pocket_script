<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/12
 * Time: 15:41
 */

namespace App\Library;

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

}