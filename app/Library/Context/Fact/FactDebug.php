<?php
namespace App\Libiary\Context\Fact;

class FactDebug extends FactBase
{
    private static $self = null;

    public function __construct()
    {
    }

    /**
     * @return null|FactError
     */
    public static function instance()
    {
        if (is_null(self::$self)) {
            self::$self = new self();
        }

        return self::$self;
    }

    public function name()
    {
        return "fact_debug";
    }

    public function debug($label, $info)
    {
        $debugInfo = array_merge(array('debug_label' => $label), $info);
        $this->record(
            $debugInfo
        );
    }
}