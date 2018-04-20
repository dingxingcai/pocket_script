<?php
namespace App\ETL\Input;

trait Marker
{
    protected $marker;
    public function setMarker($marker)
    {
        $this->marker = $marker;
    }

    public function getMarker()
    {
        return $this->marker;
    }
}