<?php
namespace App\ETL\Output;

interface IOutput
{
    public function push($data);
}