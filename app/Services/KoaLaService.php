<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class KoaLaService
{
    public static function loginCookie()
    {
        $jar = \Cache::get('face_cookie');
        if(empty($jar)){
            $jar = new CookieJar();
            $data = ['username' => env('KOALA_USENAME'), 'password' => env('KOALA_PASSWORD')];

            self::request('POST','/auth/login',['form_params' => $data, 'cookies' => $jar]);

            \Cache::put('face_cookie', $jar, 10);
        }

        return $jar;
    }

    public static function subjectPhoto($photo)
    {
        $res = KoaLaService::upload(
            '/subject/photo',
            ['photo' => $photo],
            []
        );

        return $res['data'];
    }

    public static function subjectGetByName($name)
    {
        $res = KoaLaService::get('/mobile-admin/subjects/list', ['category' => 'employee', 'name' => $name]);
        return count($res['data']) > 0 ? $res['data'][0] : [];
    }

    public static function subjectGet($id)
    {
        $res = self::request('GET', "/subject/$id", []);
        return $res['data'];
    }

    public static function subjectPut($id, $params)
    {
        $res = self::request('PUT', "/subject/$id", ['json' => $params]);
        return !empty($res['data']) ? $res['data'] : [];
    }


    public static function subjectPost($params)
    {
        $res = self::request('POST', "/subject", ['form_params' => $params]);
        return !empty($res['data']) ? $res['data'] : [];
    }

    public static function upload($path, $files, $data)
    {
        $multipart = [];

        foreach ($files as $filename => $fileResource){
            if(!is_resource($fileResource)){
                $fileResource = fopen($fileResource, 'r');
            }
            $multipart[] = [
                'name' => $filename,
                'contents' => $fileResource
            ];
        }

        foreach ($data as $key => $value){
            $multipart[] = [
                'name' => $key,
                'contents' => $value
            ];
        };

        return self::request('POST', $path, ['multipart' => $multipart]);
    }

    public static function post($path , $data)
    {
        return self::request('POST', $path, ['form_params' => $data]);
    }

    public static function get($path, $data)
    {
        return self::request('GET', $path, ['query' => $data]);
    }

    public static function request($method, $path, $options)
    {
        $baseUri = env('KOALA_HOST');
        $client = new Client(['base_uri' => $baseUri]);

        empty($options['cookies']) && ($options['cookies'] = self::loginCookie());

        empty($options['headers']) && $options['headers'] = [];
        empty($options['headers']['User-Agent']) && $options['headers']['User-Agent'] = 'Koala Admin';

        $response = $client->request($method, $path, $options);

        $resJson = $response->getBody()->getContents();
        $res = json_decode($resJson, true);
        if(empty($res)){
            throw new \Exception("Koala返回结果为非Json格式");
        }

        if($res['code'] <> 0){
            throw new \Exception($res['desc']?:'Koala结果返回未知异常', $res['code']);
        }

        return $res;
    }
}

