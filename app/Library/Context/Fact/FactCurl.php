<?php
namespace App\Libiary\Context\Fact;

class FactCurl extends FactBase
{
    private static $self = null;

    /**
     * @return null|FactCurl
     */
    public static function instance()
    {
        if(is_null(self::$self)){
            self::$self = new self();
        }

        return self::$self;
    }

    /**
     * 获取名称
     * @return string
     */
    public function name()
    {
        return "fact_curl";
    }

    public function recordCH($ch, $request, $response)
    {
        $ops = curl_getinfo($ch);

        $ops['request'] = $request;
        $ops['response'] = $response;
        $ops['errno'] = curl_errno($ch);
        $ops['error'] = curl_error($ch);

        $this->record($ops);
    }
}