<?php
namespace App\ETL;

use App\ETL\Buffer\IBuffer;
use App\ETL\Buffer\OneDimensionalArray;
use App\ETL\Input\IInput;
use App\ETL\Input\NullInput;
use App\ETL\Output\IOutput;
use App\ETL\Output\NullOutput;

class ETL
{
    /**
     * @var IInput
     */
    private $input = array();
    /**
     * @var IOutput
     */
    private $output = array();

    /**
     * @param IInput $input
     * @return $this
     */
    public function setInput(IInput $input)
    {
        $this->input = $input;
        return $this;
    }
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param IOutput $output
     * @return $this
     */
    public function setOutput(IOutput $output)
    {
        $this->output = $output;
        return $this;
    }
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @var IBuffer
     */
    private $buffer = null;
    public function setBuffer(IBuffer $buffer)
    {
        $this->buffer = $buffer;
        return $this;
    }

    /**
     * @var int
     */
    public $limit = 0;
    public function setLimit($limit)
    {
        $this->limit = max($limit, 1);
        return $this;
    }

    /**
     * @var int
     */
    public $upper = 0;
    public function setUpper($upper)
    {
        $this->upper = $upper;
        return $this;
    }

    /**
     * @var null
     */
    private $before = null;
    public function setBefore(\Closure $before)
    {
        $this->before = $before;
        return $this;
    }

    /**
     * @var null
     */
    private $after = null;
    public function setAfter(\Closure $after)
    {
        $this->after = $after;
        return $this;
    }

    /**
     * @var null
     */
    private $fail = null;
    public function setFail(\Closure $fail)
    {
        $this->fail = $fail;
        return $this;
    }

    /**
     * 是否是额的
     * @return bool
     */
    public function hungry()
    {
        return $this->lastLoaded < $this->limit;
    }

    /**
     * 是否是饱的
     * @return bool
     */
    public function full()
    {
        return $this->lastLoaded >= $this->upper;
    }

    /**
     * @var null
     */
    private $beforePush = null;
    public function setBeforePush(\Closure $beforePush)
    {
        $this->beforePush = $beforePush;
        return $this;
    }

    private $transactions = [];
    public function pushTransaction(\Closure $transaction)
    {
        $this->transactions[] = $transaction;
    }

    private $stage = null;
    public function setStage($stage)
    {
        $this->stage = $stage;
    }
    public function getStage()
    {
        return $this->stage;
    }

    public $loaded = 0;
    public $lastLoaded = 0;

    public $params = [];

    public function run()
    {
        try{
            isset($this->before) && call_user_func($this->before, $this);

            while ($this->loaded < $this->upper){

                while(!$this->buffer->isFull()){
                    $data = $this->input->pull($this->limit, $this->params);

                    $this->lastLoaded = count($data);

                    $this->loaded += $this->lastLoaded;

                    $this->buffer->push($data);

                    unset($data);

                    if($this->lastLoaded < $this->limit){
                        break;
                    }
                }
                \Log::info("once pull completed: limit:{$this->limit}, upper:{$this->upper}, lastLoaded:{$this->lastLoaded},loaded:{$this->loaded}");
                while(!$this->buffer->isComplete()){
                    $data = $this->buffer->pull();

                    /**
                     * 循环处理数据
                     */
                    foreach ($this->transactions as $transaction){
                        $res = call_user_func($transaction, $this, $data);
                        !is_null($res) && $data = $res;
                    }

                    isset($this->beforePush) && call_user_func($this->beforePush, $this, $data);

                    $this->output->push($data);

                    unset($data);
                }

                \Log::info("once push completed!");
                $this->buffer->clear();

                if($this->lastLoaded < $this->limit){
                    break;
                }
            };

            isset($this->after) && call_user_func($this->after, $this);
        }catch (\Exception $e){
            isset($this->fail) && call_user_func($this->fail, $this, $e);
            \Log::info("EtlErrorHappened!: {$e->getMessage()}");
            throw $e;
        }
    }

    public static function constructFromCfg($cfg)
    {
        $etl = new ETL();

        $input = isset($cfg['input']) && is_callable($cfg['input']) ? call_user_func($cfg['input']) : new NullInput();
        $etl->setInput($input);
        $output = isset($cfg['output']) && is_callable($cfg['output']) ? call_user_func($cfg['output']) : new NullOutput();
        $etl->setOutput($output);

        $limit = is_callable($cfg['limit']) ? call_user_func($cfg['limit']) : $cfg['limit'];
        $etl->setLimit($limit);

        $upper = is_callable($cfg['upper']) ? call_user_func($cfg['upper']) : $cfg['upper'];
        $etl->setUpper($upper);

        $buffer = isset($cfg['buffer']) && is_callable($cfg['buffer']) ? call_user_func($cfg['buffer']) : new OneDimensionalArray($limit);
        $etl->setBuffer($buffer);

        isset($cfg['before']) && is_callable($cfg['before']) && $etl->setBefore($cfg['before']);
        isset($cfg['after']) && is_callable($cfg['after']) && $etl->setAfter($cfg['after']);
        isset($cfg['fail']) && is_callable($cfg['fail']) && $etl->setFail($cfg['fail']);
        isset($cfg['beforePush']) && is_callable($cfg['beforePush']) && $etl->setBeforePush($cfg['beforePush']);

        if(isset($cfg['transactions'])){
            $transactions = is_array($cfg['transactions']) ?: [$cfg['transactions']];
            foreach ($transactions as $transaction){
                is_callable($transaction) && $etl->pushTransaction($transaction);
            }
        }

        isset($cfg['params']) && $etl->params = $cfg['params'];

        return $etl;
    }
}
