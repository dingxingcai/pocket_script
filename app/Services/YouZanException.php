<?php

namespace App\Services;

class YouZanException extends \Exception
{
    public function __construct(array $errorResponse)
    {
        $msg = '找不见错误原因的字段';
        !empty($errorResponse['message']) && $msg = $errorResponse['message'];
        !empty($errorResponse['msg']) && $msg = $errorResponse['msg'];

        parent::__construct($msg, $errorResponse['code'], null);
    }
}
