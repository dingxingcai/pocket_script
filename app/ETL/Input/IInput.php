<?php
namespace App\ETL\Input;

interface IInput
{
    public function pull($limit, $params);
}