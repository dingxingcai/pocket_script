<?php
namespace App\ETL\Output;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;

class ESSingleType implements IOutput
{
    protected $initMemory = 0 ;

    protected $hosts = null;
    /**
     * @var Client
     */
    protected $client = null;
    protected $params = array();

    protected $memoryLimit = 70000000;

    protected $routingKey = null;
    protected $parentKey = null;

    public function __construct($hosts, $primaryKey, $index, $type, $routingKey = null,$memoryLimit = null, $parentKey = null)
    {
        $this->hosts = $hosts;
        $this->client = ClientBuilder::create()->setHosts($this->hosts)->build();

        $this->params = array('index' => $index, 'type' => $type, 'body' => array());
        $this->primaryKey = $primaryKey;
        $this->routingKey = $routingKey;
        $this->parentKey = $parentKey;

        $this->memoryLimit = $memoryLimit;
        $this->initMemory = memory_get_usage();
    }

    private function reInstanceClientForMemoryLeak()
    {
        $curMemory = memory_get_usage();
        if($curMemory - $this->initMemory > $this->memoryLimit){
            \Log::info('reInstanceClientForMemoryLeak');
            unset($this->client);
            $this->client = ClientBuilder::create()->setHosts($this->hosts)->build();
        }
    }

    public function push($aData)
    {
        foreach($aData as $data) {
            $updateMeta = array('_id' => $data[$this->primaryKey]);
            !is_null($this->routingKey) && $updateMeta['routing'] = $data[$this->routingKey];
            if (!is_null($this->parentKey)) {
                $updateMeta['parent'] = $data[$this->parentKey];
            }
            $this->params['body'][] = array('update' => $updateMeta);
            $this->params['body'][] = array('doc' => $data, 'doc_as_upsert' => true, 'field' => '_source');
        }
        \Log::info("start push data to es : " . count($this->params['body']) / 2);
        $response = $this->client->bulk($this->params);
        $this->logMemory();

        $this->reInstanceClientForMemoryLeak();

        unset($this->params['body']);

        if($response['errors']){
            foreach($response['items'] as &$singleItem){
                $currentData = current($singleItem);
                if($currentData['error']){
                    \Log::error('push to es failed', $currentData);
                    throw new \Exception($currentData['error']);
                }
            }
        }
        unset($response);
    }

    protected function logMemory()
    {
        $curMemory = memory_get_usage();
        \Log::info(sprintf("memory diff: %s, cur: %s, init: %s",
            $curMemory - $this->initMemory, $curMemory, $this->initMemory));
    }
}