<?php

namespace App\Library\Utility;

class CurlWrapper
{
    /**
     * post数据
     * @param $data
     * @param $url
     * @param int $second
     * @return mixed
     * @throws \Exception
     */
    public static function post($data, $url, $second = 30, $header=null)
    {
        $queries = is_scalar($data) ? strval($data) : http_build_query($data);

        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);

        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $queries);

        if(!is_null($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //运行curl
        $data = curl_exec($ch);

//        FactCurl::instance()->recordCH($ch, $queries, $data);

        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            $msg = curl_error($ch);
            curl_close($ch);
            throw new \Exception("curl出错:" . $msg, $error);
        }
    }

    /**
     * post数据
     * @param $data
     * @param $url
     * @param int $second
     * @return mixed
     * @throws \Exception
     */
    public static function get($data, $url, $second = 30, $headers=null)
    {
        $queries = is_scalar($data) ? strval($data) : http_build_query($data);

        $ch = curl_init();
        $urlFull = "$url?$queries";
        curl_setopt($ch, CURLOPT_URL, $urlFull);

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        is_null($headers) && $headers=[];
        $headers[] = 'Expect:';

        // disable 100-continue
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        //运行curl
        $data = curl_exec($ch);

//        FactCurl::instance()->recordCH($ch, $queries, $data);

        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            $msg = curl_error($ch);
            curl_close($ch);
            throw new \Exception("curl出错:" . $msg, $error);
        }
    }

    /**
     * 访问账户中心
     * @param $data
     * @param $url
     * @param int $second
     * @return mixed
     * @throws \Exception
     */
    public static function callZZAccountPay($data, $url, $second=30)
    {
        $rawRes = self::post($data, $url, $second);

        $res = json_decode($rawRes, true);

        if(empty($res) || !isset($res['status'])){
            throw new \Exception('账户中心返回结果异常!');
        }

        if($res['status'] !== 0){
            throw new \Exception(!empty($res['msg']) ? $res['msg'] : '账户中心请求处理失败!');
        }

        return $res['data'];
    }

    /**
     * 访问白条支付-支付网关
     * @param $data
     * @param $url
     * @param int $second
     * @return mixed
     * @throws \Exception
     */
    public static function callZZBaiTiaoPay($data, $url, $second=30)
    {
        $rawRes = self::post($data, $url, $second);

        $res = json_decode($rawRes, true);

        if(empty($res) || !isset($res['status'])){
            throw new \Exception('白条支付(业务网关)返回结果异常!');
        }

        if($res['status'] !== 0){
            throw new \Exception(!empty($res['msg']) ? $res['msg'] : '白条支付(业务网关)请求处理失败!');
        }

        return $res['data'];
    }

    public static function curlGet($url){
        $curl = curl_init();
        // 设置你需要抓取的URL
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); //连接超时
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); //执行超时
        curl_setopt($curl, CURLOPT_URL, $url); //访问的url
        curl_setopt($curl, CURLOPT_HTTPHEADER , array('Expect:'));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // 运行cURL，请求网页
        $data = curl_exec($curl);
        // 关闭URL请求
        curl_close($curl);
        return $data;
    }
}
