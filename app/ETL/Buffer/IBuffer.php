<?php
namespace App\ETL\Buffer;

use ETL\IInput;
use ETL\IOutput;

interface IBuffer
{
    public function pull();

    public function push($data);

    public function isFull();
    
    public function isComplete();

    public function clear();
}