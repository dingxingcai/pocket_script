<?php

namespace App\Services;

use App\Libiary\Utility\CurlWrapper;

class GuanJiaPoService
{

    public static function getLingShouDanByMobile($mobile)
    {
        $url = env('GUANJIAPO_HOST') . '/tdy/RetailBillByVip/RetailBillByVip';

        $sercet = "Grasp010-00333";
        $date = date("Ymd");
        $md5Before = "{$sercet}{$mobile}{$date}";
        $md5 = strtoupper(md5($md5Before));

        $resStr = CurlWrapper::get(['mobile' => $mobile, 'signature' => $md5], $url);

        $resJson = json_decode($resStr, true);
        if (!isset($resJson['Code']) || ($resJson['Code'] <> 1 && $resJson['Msg'] <> '该会员不存在！请输入正确的手机号码！')) {
            throw new \Exception("管家婆接口调用错误：" . $resJson['Msg'], $resJson['Code']);
        }
        return !empty($resJson['Data']) ? $resJson['Data'] : [];
    }

    public static function getLingShouTuiHuoDanByMobile($mobile)
    {
        $url = env('GUANJIAPO_HOST') . '/tdy/RefundRBbyVip/RefundRBbyVip';

        $sercet = "Grasp010-00333";
        $date = date("Ymd");
        $signature = strtoupper(md5("{$sercet}{$mobile}{$date}"));

        $resStr = CurlWrapper::get(['mobile' => $mobile, 'signature' => $signature], $url);

        $resJson = json_decode($resStr, true);
        if (!isset($resJson['Code']) || ($resJson['Code'] <> 1 && $resJson['Msg'] <> '该会员不存在！请输入正确的手机号码！')) {
            throw new \Exception("管家婆接口调用错误：" . $resJson['Msg'], $resJson['Code']);
        }
        return !empty($resJson['Data']) ? $resJson['Data'] : [];
    }

    public static function grantVip($mobile, $cardName)
    {
        $url = env('GUANJIAPO_HOST') . '/tdy/VipAuthorization/VipAuthorization';

        $sercet = "Grasp010-00334";
        $date = date("Ymd");
        $md5 = strtoupper(md5("{$sercet}{$mobile}{$cardName}{$date}"));

        $resStr = CurlWrapper::get(['mobile' => $mobile, 'signature' => $md5, 'card_name' => $cardName], $url);

        $resJson = json_decode($resStr, true);
        if (empty($resJson['Code']) || $resJson['Code'] <> 1) {
            throw new \Exception("
            
            
            ：" . $resJson['Msg'], $resJson['Code']);
        }
        return $resJson;
    }

    public static function syncPoints($mobile, $points)
    {
        $url = env('GUANJIAPO_HOST') . '/tdy/Integral/ModifyIntegral';

        $sercet = "Grasp010-00335";
        $date = date("Ymd");
        $signature = strtoupper(md5("{$sercet}{$mobile}{$points}{$date}"));

        $resStr = CurlWrapper::get(
            ['vipcardcode' => $mobile, 'signature' => $signature, 'integral' => strval($points)],
            $url);

        $resJson = json_decode($resStr, true);
        if (!isset($resJson['status']) || $resJson['status'] <> 0) {
            throw new \Exception("管家婆接口调用错误：" . $resJson['message'], $resJson['status']);
        }
        return $resJson;
    }
}

