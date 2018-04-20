<?php
namespace App\ETL\Buffer;


class OneDimensionalArray implements IBuffer
{
    private $data = array();
    private $step = 0;
    private $bufferSize;

    public static function instanceFromOption($option)
    {
        $option = array_merge(array('size' => 10000, 'step' => null), $option);
        return new self($option['size'], $option['step']);
    }

    public function __construct($bufferSize, $step = null)
    {
        $this->bufferSize = $bufferSize;
        $this->step = isset($step) ? $step : $this->bufferSize;
    }

    public function pull()
    {
        $data = array_slice($this->data, 0, $this->step);
        $this->data = array_slice($this->data, $this->step);
        return $data;
    }

    public function push($data)
    {
        $this->data = array_merge($this->data, $data);
    }

    public function isFull()
    {
        return count($this->data) >= $this->bufferSize;
    }

    public function isComplete()
    {
        return empty($this->data);
    }

    public function clear()
    {
        unset($this->data);
        $this->data = array();
    }
}