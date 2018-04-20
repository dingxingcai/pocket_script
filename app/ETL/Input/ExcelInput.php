<?php
namespace App\ETL\Input;


class ExcelInput implements IInput
{
    private $filename = null;
    private $hasLoaded = false;
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function pull($limit, $params)
    {
        $result = \Excel::selectSheetsByIndex(0)->load($this->filename)->get()->toArray();
        $this->hasLoaded = true;

        return $result;
    }

    public function isComplete()
    {
        return $this->hasLoaded;
    }
}