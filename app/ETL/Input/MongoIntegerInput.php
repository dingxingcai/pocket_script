<?php
namespace ETL\Input;

use ETL\IInput;
use ETL\KPI;
use Illuminate\Database\Connection;
use Jenssegers\Mongodb\Query\Builder;

class MongoIntegerInput implements IInput
{
    /**
     * @var Builder
     */
    protected $queryBuilder = null;

    protected $kpiKey = null;

    protected $offset = 0;
    protected $limit = 0;

    protected $srcBuilder = null;
    protected $lastDataCount = 0;
    protected $filter = null;

    public function __construct(Builder $queryBuilder, $take, $kpiKey=null, $filter=null)
    {
        $this->srcBuilder = $queryBuilder;
        $this->limit = $this->lastDataCount = $take;
        $this->kpiKey = $kpiKey;
        $this->filter = $filter;
    }

    public function pull()
    {
        \Log::info("start pull data ", array('offset' => $this->offset, 'limit' => $this->limit, 'lastDataCount' => $this->lastDataCount));

        $result = $this->queryBuilder->skip($this->offset)->take($this->limit)->get();

        if(isset($this->filter)){
            array_walk($result, $this->filter);
        }
        $this->offset += $this->limit;

        $this->lastDataCount = count($result);

        \Log::info("end pull data ", array('offset' => $this->offset, 'limit' => $this->limit, 'lastDataCount' => $this->lastDataCount));

        return $result;
    }

    public function isComplete()
    {
        return $this->lastDataCount < $this->limit;
    }

    public function setKpi(KPI $kpi = null)
    {
        $this->offset = 0;
        $this->lastDataCount = $this->limit;

        unset($this->queryBuilder);
        $this->queryBuilder = clone $this->srcBuilder;
        if(isset($kpi) && isset($this->kpiKey)){
            $this->queryBuilder
                ->where($this->kpiKey, '>=', $kpi->getStart())
                ->where($this->kpiKey, '<', $kpi->getEnd())
            ;
        }
    }
}