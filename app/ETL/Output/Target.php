<?php
namespace App\ETL\Output;

trait Target
{
    protected $target;
    public function setTarget($target)
    {
        $this->target = $target;
    }

    public function getTarget()
    {
        return $this->target;
    }
}