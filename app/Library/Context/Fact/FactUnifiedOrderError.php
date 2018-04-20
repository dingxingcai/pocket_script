<?php
namespace App\Libiary\Context\Fact;

use App\Link;

class FactUnifiedOrderError extends FactBase
{
    private static $self = null;

    /**
     * @return null|FactUnifiedOrderError
     */
    public static function instance()
    {
        if(is_null(self::$self)){
            self::$self = new self();
        }

        return self::$self;
    }

    public function name()
    {
        return "fact_unified_order_error";
    }

    /**
     * @param \Exception $exception
     * @throws \Exception
     */
    public function recordLinkAndException(Link $link, \Exception $exception)
    {
        $data = array(
            'code' => $exception->getCode(),
            'msg' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
            'class' => get_class($exception),
            );
        if($link->Channel){
            $data['provider_id'] = $link->Channel->provider_id;
            $data['method_id'] = $link->Channel->method_id;
            $data['link'] = $link->toJson();
        }

        $this->record($data);
    }
}