<?php
namespace App\ETL\Output;

class MergedArray implements IOutput
{
    private $storage = array();

    public function __construct(&$storage)
    {
        $this->storage = &$storage;
    }

    public function push($aData)
    {
        $this->storage = array_merge($this->storage, $aData);
    }
}