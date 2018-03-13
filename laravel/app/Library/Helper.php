<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/12
 * Time: 15:41
 */

namespace App\Library;


class Helper
{

    //生成token
    public static  function token($id){
        $time = substr(microtime(false),0,5);
        return md5($time.$id);
    }

}