<?php

namespace App\Services;

use App\Libiary\Utility\CurlWrapper;

class ZuLinService
{
    public static function grantVip($mobile, $cardName)
    {
        $params = [
            'mobile' => $mobile,
            'card' => $cardName
        ];

        return self::post($params, '/data-center/user/card');
    }

    public static function syncPoints($mobile, $points)
    {
        $params = [
            'mobile' => $mobile,
            'point' => $points
        ];

        return self::post($params, '/data-center/user/point');
    }

    private static function post($params, $path)
    {
        $url = env('ZULIN_HOST') . $path;
        $key = env('ZULIN_KEY');

        $sign = self::sign($params, $key);
        $params['sign'] = $sign;
        $resStr = CurlWrapper::post($params, $url);

        $resJson = json_decode($resStr, true);
        if (empty($resJson['return_code']) || ($resJson['return_code'] <> 'SUCCESS' && $resJson['sub_code'] <> 'USER_NOT_FOUND')) {
            throw new \Exception("租赁接口调用错误：" . $resJson['message']);
        }
        return $resJson;
    }

    public static function verify($params, $appsecret)
    {
        $sign = $params['sign'];
        unset($params['sign']);
        if ($sign !== self::sign($params, $appsecret)) {
            throw new \Exception("sign verify error!");
        }
    }

    public static function sign($params, $appsecret)
    {
        unset($params['sign']);
        $stringPrepare = self::getSignContent($params);
        $stringToBeSigned = "{$stringPrepare}&key={$appsecret}";
        return strtoupper(md5($stringToBeSigned));
    }

    #排序参数
    public static function getSignContent($params)
    {
        ksort ( $params );
        $stringToBeSigned = "";
        $i = 0;
        foreach ( $params as $k => $v ) {
            if (is_scalar($v) && false === self::checkEmpty ( $v ) && "@" != substr ( $v, 0, 1 )) {
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i ++;
            }
        }
        unset ( $k, $v );
        return $stringToBeSigned;
    }
    /**
     * 校验$value是否非空
     * if not set ,return true;
     * if is null , return true;
     */
    public static function checkEmpty($value) {
        if (! isset ( $value ))
            return true;
        if ($value === null)
            return true;
        if (trim ( $value ) === "")
            return true;
        return false;
    }
}

