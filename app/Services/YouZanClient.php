<?php

namespace App\Services;

use Youzan\Open\Client;

class YouZanClient extends Client
{
    public function get($method, $apiVersion, $params = array())
    {
        $response = parent::get($method, $apiVersion, $params);

        if(isset($response['error_response'])){
            throw new YouZanException($response['error_response']);
        }else{
            return $response['response'];
        }
    }

    public function post($method, $apiVersion, $params = array(), $files = array())
    {
        $response = parent::post($method, $apiVersion, $params, $files);

        if(isset($response['error_response'])){
            throw new YouZanException($response['error_response']);
        }else{
            return $response['response'];
        }
    }
}