<?php
namespace App\Libiary\Context;

use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use App\Libiary\Context\Dimension\DimExecution;
use App\Libiary\Context\Entry\EntryBase;
use App\Libiary\Context\Fact\FactError;
use App\Libiary\Context\Handler\EntryRecordFileHandler;
use App\Libiary\Context\UUID\Snowflake;

class Log
{
    private static $self = null;


    private $isInit = false;
    /**
     * 是否初始化
     */
    public function isInit()
    {
        return $this->isInit;
    }

    /**
     * @param $app
     * @param $machineId
     * @return null|Log
     */
    public static function instance()
    {
        if(is_null(self::$self)){
            self::$self = new self();
        }
        return self::$self;
    }

    /**
     * 此次的执行ID
     * @return null
     */
    public function eid()
    {
        return $this->eid;
    }

    public function mid()
    {
        return $this->mid;
    }

    /**
     * 产生一次rid
     * @return int
     */
    public function generateRid()
    {
        return Snowflake::getGenerator()->generate($this->mid);
    }

    private $eid = null;
    private $mid = null;

    /**
     * @var BlackList
     */
    private $blackList = null;

    private function __construct()
    {
        $this->blackList = new BlackList();
    }
    /**
     * @var EntryRecordFileHandler
     */
    private $entryHandler = null;

    private $dumpStep = null;
    /**
     * 初始化
     * @param $prefix
     * @param $machineId
     */
    public function init($prefix, $path, $dumpStep=null)
    {
        $this->mid = $this->generateMachineId();

//        产生eid
        $this->eid = Snowflake::getGenerator()->generate($this->mid);

//        构建entry的记录Handler - 因为脚本在B端用root跑,Rundeck用www跑,所以这里需要将文件设置成777都可写
        $this->entryHandler = new EntryRecordFileHandler("{$path}/{$prefix}", 0, Logger::DEBUG, true, 0777);
        $this->entryHandler->setFormatter(new JsonFormatter(JsonFormatter::BATCH_MODE_NEWLINES));

//        注册close函数
        register_shutdown_function(array($this, 'close'));

//        增加执行参数,移除到外面执行
//        DimExecution::instance()->recordBegin();

//        注册错误信息 - 这部分信息貌似不会阻碍程序执行,故这里不再继续此日志
//        FactError::instance()->registerErrorAndExceptionHandler();

        $this->isInit = true;

        $this->dumpStep = is_null($dumpStep) ? 9999 : $dumpStep;
    }

    private $entryBuffer = array();

    /**
     * 记录Entry信息
     * @param EntryBase $entry
     */
    public function recordEntry(EntryBase $entry, $data)
    {
        if(!$this->isInit){
            return false;
        }

        $name = $entry->name();
        $key = $entry->getIdentity();

        if(isset($this->entryBuffer[$key])) {
            $newRecord = $this->entryBuffer[$key];
            $newRecord[$name] = array_merge($newRecord[$name], $data);
        }else{
            $newRecord = array();
            $newRecord['eid'] = Log::instance()->eid();
            $newRecord['rid'] = $entry->rid();
            $newRecord['entry'] = $name;
            $newRecord[$name] = $data;
        }

        /**
         * 重新更新时间
         */
        $newRecord['time'] = date("Y-m-d H:i:s");

        $this->entryBuffer[$key] = $newRecord;

        (count($this->entryBuffer) > $this->dumpStep) && $this->dumpBuffer();
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        FactError::instance()->checkAndRecordErrorCatchByShutdown();

        $this->dumpBuffer();
    }

    public function dumpBuffer()
    {
        /**
         * TBD,这样获取mca和app_id的方式很丑陋
         */
        if(!$this->blackList->discard(DimExecution::instance())){
            $this->entryHandler->handleBatch($this->entryBuffer);
        }
        $this->entryBuffer=[];
    }

    /**
     * 一定要确认这里的machine不重复,且小于64,手段比较傀儡,
     * 但先这样吧,预期线上app事务日志不会有超过63台
     * @var array
     */
    private $hostMachineIdMap = array(
        'clientC11' => 11,
        'clientC12' => 12,
        'clientA15' => 15,
        'clientA16' => 16,
        'clientB17' => 17,
        'wby-backup-b17' => 18,
        'sms_api52' => 52,
        'oauth_login53' => 53,
        'online_storage205' => 62,
    );

    public function generateMachineId()
    {
        $hostname = gethostname();
        if(!isset($this->hostMachineIdMap[$hostname])){
            return 63;
        }
        else{
            return $this->hostMachineIdMap[$hostname];
        }
    }
}