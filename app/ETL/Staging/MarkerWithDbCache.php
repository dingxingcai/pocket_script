<?php

namespace App\ETL\Staging;

use App\ETL\ETL;
use App\ETL\Input\Marker;

class MarkerWithDbCache
{
    protected $marker = '';
    protected $context = '';
    protected $state = '';

    protected $name = '';
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function isIdle()
    {
//        return is_null($this->state)
        return false;
    }

    public function loadToEtl(ETL $etl)
    {
        $input = $etl->getInput();
        if($input instanceof Marker) {
            $marker = \Cache::store('database')->get($this->name);
            !empty($marker) && $input->setMarker($marker['marker']);
        }else{
            throw new \Exception("input must instance of marker");
        }
    }

    public function markFromEtl(ETL $etl, $context)
    {
        $input = $etl->getInput();
        if($input instanceof Marker) {
            $marker = $input->getMarker();
            \Cache::store('database')->set($this->name, ['marker' => $marker, 'context' => $context]);
        }else{
            throw new \Exception("input must instance of marker");
        }
    }
}