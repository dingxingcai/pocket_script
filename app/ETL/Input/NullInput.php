<?php
namespace App\ETL\Input;

class NullInput implements IInput
{
    public function pull($limit, $params)
    {
        return [];
    }
}