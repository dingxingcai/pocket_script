<?php
namespace App\Libiary\Context\Fact;

use App\Libiary\Context\Dimension\DimExecution;

class FactException extends FactBase
{
    private static $self = null;

    /**
     * @return null|FactException
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
        return "fact_exception";
    }

    /**
     * @param \Exception $exception
     * @throws \Exception
     */
    public function recordException(\Exception $exception, $tag='unknown')
    {
        $dimExecution = DimExecution::instance();
        $this->record(
            array(
                'code' => $exception->getCode(),
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'class' => get_class($exception),
                'mca' => $dimExecution->property(DimExecution::PROPERTY_MCA, ''),
                'user_id' => $dimExecution->property(DimExecution::PROPERTY_USER_ID, ''),
                'ip' => $dimExecution->property(DimExecution::PROPERTY_IP, ''),
                'url' => $dimExecution->property(DimExecution::PROPERTY_URL, ''),
                'user_agent' => $dimExecution->property(DimExecution::PROPERTY_USER_AGENT, ''),
                'app_id' => $dimExecution->property(DimExecution::PROPERTY_APP_ID, ''),
                'tag' => $tag
            )
        );
    }
}