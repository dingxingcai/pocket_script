<?php
namespace App\ETL\Output;

class CompositeSerially implements IOutput
{
    protected $dataSplitter = null;

    /**
     * @var IOutput[]
     */
    protected $outs = array();

    public function __construct($outs, $dataSplitter)
    {
        $this->outs = $outs;

        if(!is_callable($dataSplitter)){
            throw new \Exception('dataSplitter must be a callback');
        }
        $this->dataSplitter = $dataSplitter;
    }

    public function push($unSplitData)
    {
        $datas = call_user_func($this->dataSplitter, $unSplitData);

        foreach($datas as $key => $data){
            if(!isset($this->outs[$key])){
                throw new \Exception("the out key of {$key} is not existed");
            }

            $this->outs[$key]->push($data);
        }
    }

    public function getOuts()
    {
        return $this->outs;
    }
}