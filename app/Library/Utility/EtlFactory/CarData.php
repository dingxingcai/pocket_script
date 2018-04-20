<?php

namespace App\Utility\EtlFactory;

use App\Console\Commands\ETL\ETLRecordClean;
use App\Console\Commands\ETL\ETLRunner;
use App\ETL\Buffer\LaravelQueueWithRedis;
use App\ETL\ETL;
use App\ETL\Input\PdoWithLaravel;
use App\ETL\Input\SAEStorage;
use App\ETL\Output\CompositeSerially;
use App\ETL\Output\MysqlInsertWithPdo;
use App\ETL\Staging\MultiTableWithMysqlPdo;
use App\EtlRunRecord;
use App\Jobs\LoadCarDataFromSAEStorageToRedisQueue;
use App\Jobs\LoadUserDataFromSAEStorageToRedisQueue;
use App\Utility\EtlConstant;
use App\Utility\EtlParser;
use App\Utility\EtlSchema;
use Illuminate\Console\Scheduling\Schedule;

class CarData
{
    private $delta = false;
    private $identity = false;

    public static $outMaps = [
        EtlSchema::TABLE_CARS => [],
        EtlSchema::TABLE_FUEL_RECORDS => [],
        EtlSchema::TABLE_EXPENSES => [],
        EtlSchema::TABLE_INSURANCE_REMINDERS => [],
        EtlSchema::TABLE_MAINT_DATE_REMINDERS => [],
        EtlSchema::TABLE_MAINT_ODOMETER_REMINDERS => [],
        EtlSchema::TABLE_MAINT_PERIODICAL_REMINDERS => [],
    ];

    public function __construct($delta)
    {
        $this->delta = $delta;

        $this->identity = $delta ? EtlConstant::DELTA_PARSE_CARS_FROM_STORAGE : EtlConstant::REBUILD_PARSE_CARS_FROM_STORAGE;
    }

    public static function schedule(Schedule $schedule)
    {
        $schedule->command(
            ETLRunner::class,
            ['--queue=etl.parse_car_data.delta']
        )->cron("/5 * * * *")->withoutOverlapping()->runInBackground()->when(function (){
            return \Cache::store('database')->has(EtlConstant::DELTA_PARSE_CARS_FROM_STORAGE)
               && !EtlRunRecord::isRunning(EtlConstant::REBUILD_PARSE_CARS_FROM_STORAGE);
        });

        $schedule->call(function (){
            \Cache::store('database')->forever(EtlConstant::DELTA_PARSE_CARS_FROM_STORAGE, '1');
        })->name("start_" . EtlConstant::DELTA_PARSE_CARS_FROM_STORAGE)->cron("1 3 * * *")->withoutOverlapping()->runInBackground();


        $schedule->command(
            ETLRunner::class,
            ['--queue=etl.parse_car_data.rebuild']
        )->cron("/1 8-23 * * *")->withoutOverlapping()->runInBackground()->when(function (){
            return \Cache::store('database')->has(EtlConstant::REBUILD_PARSE_CARS_FROM_STORAGE) &&
                !EtlRunRecord::isRunning(EtlConstant::DELTA_PARSE_CARS_FROM_STORAGE)
                ;
        });

//        $schedule->call(function (){
//            \Cache::store('database')->forever(EtlConstant::REBUILD_PARSE_CARS_FROM_STORAGE, '1');
//        })->cron("/1 * * * *");

        $schedule->call(function (){
            self::mergeOneEndedRecord();
        })->name(EtlConstant::MERGE_PARSE_CARS_FROM_STORAGE )->cron("31 6 * * *")->withoutOverlapping()->runInBackground();

        $schedule->command(
            ETLRecordClean::class,
            ['--identity=' . EtlConstant::DELTA_PARSE_CARS_FROM_STORAGE, '--view=false', '--state=merged', '--before=1440']
        )->cron("31 10 * * *")->withoutOverlapping()->runInBackground();
    }

    public function input($prefix = 'CarData', $step = 1000)
    {
        return function () use($prefix, $step) {
            $filter = null;

            if ($this->delta) {
                $filter = function ($objects, $params) {
                    return EtlParser::parseSaeStorageDataWithLastModified($objects, $params['dateBegin'], $params['dateEnd']);
                };
            }
            return new SAEStorage(
                config('sae.storage.accessKey'),
                config('sae.storage.secretKey'),
                'data',
                $prefix,
                $step,
                $filter,
                null
            );
        };
    }

    public function buffer($bufferSize=100000, $pullStep = 500, $dispatchQueue = EtlConstant::QUEUE_PUBLIC)
    {
        return function () use($bufferSize, $pullStep, $dispatchQueue) {
            return new LaravelQueueWithRedis(
                $bufferSize,
                $pullStep,
                2,
                function ($data) {
                    return (
                    new LoadCarDataFromSAEStorageToRedisQueue(
                        'data',
                        $data['name'],
                        $this->identity)
                    );
                },
                $dispatchQueue,
                $this->identity
            );
        };
    }

    public function output()
    {
        return function () {
            $dc = \DB::connection('dc')->getPdo();
            $outs = [];
            foreach (self::$outMaps as $table => $value) {
                $outs[$table] = new MysqlInsertWithPdo($dc, $table, null,null,false);
            }

            return new CompositeSerially($outs, function ($aData) {
                $res = self::$outMaps;
                $keys = array_keys($res);

                foreach ($aData as $data) {
                    $rawData = json_decode($data, true);

                    foreach ($keys as $key) {
                        if (isset($rawData[$key]) && count($rawData[$key]) > 0) {
                            foreach ($rawData[$key] as $value) {
                                $res[$key][] = $value;
                            }
                        }
                    }
                }
                return $res;
            });
        };
    }

    public function before($initDate='2017-10-20')
    {
        return function (ETL $etl) use($initDate) {
            EtlRunRecord::createOrWake(
                $this->identity,
                $etl,
                function (EtlRunRecord $record=null, EtlRunRecord $lastRecord=null) use($initDate){
                    $postfix = $this->delta ? '_delta' : '';

                    $record->params = [
                        'dateBegin' => !empty($lastRecord) ? $lastRecord->params['dateEnd'] : $initDate,
                        'dateEnd' => date("Y-m-d H:i:s")
                    ];

                    $record->marker = null;

                    $stage = new MultiTableWithMysqlPdo('dc',
                        EtlSchema::schemaMaps(array_keys(self::$outMaps), $postfix),
                        '_' . date("YmdHis")
                    );
                    $stage->staging();

                    $record->stage = $stage;
                },
                function (EtlRunRecord $record)use ($etl){
                    $postfix = $this->delta ? '_delta' : '';

                    /** @var MultiTableWithMysqlPdo $stage */
                    $stage = $record->stage;

                    /** @var CompositeSerially $out */
                    $out = $etl->getOutput();
                    foreach ($out->getOuts() as $table => $output) {
                        $output->setTarget($stage->stage($table . $postfix)->getStaging());
                    }
                }
            );
        };
    }

    public function after()
    {
        return function (ETL $etl) {
            EtlRunRecord::endOrSleep($this->identity, $etl, function (EtlRunRecord $record){
                if(!$this->delta){
                    /** @var MultiTableWithMysqlPdo $stage */
                    $stage = $record->stage;
                    $stage->swap();
                }

                \Cache::store('database')->forget($this->identity);
            });
        };
    }

    public function fail()
    {
        return function (ETL $etl, \Exception $e) {
            EtlRunRecord::fail($this->identity, $etl);
        };
    }

    public static function mergeOneEndedRecord()
    {
        $deltaRecord = EtlRunRecord::fetchOneEnd(EtlConstant::DELTA_PARSE_CARS_FROM_STORAGE);
        if(empty($deltaRecord)){
            return;
        }
        /** @var MultiTableWithMysqlPdo $stage */
        $stage = $deltaRecord->stage;

        $postfix = '_delta';

        foreach (array_keys(self::$outMaps) as $rebuild){
            $stageKey = $rebuild . $postfix;
            $delta = $stage->stage($stageKey)->getStaging();

            self::mergeOneTable($delta, $rebuild);
        }

        $deltaRecord->state = EtlRunRecord::STATE_MERGED;

        $deltaRecord->save();
    }

    public static function mergeOneTable($delta, $rebuild, $step = 5000)
    {
        $sql = <<<SQL
    select COLUMN_NAME from information_schema.columns where table_name='{$delta}' AND COLUMN_NAME <> 'n_id'
SQL;

        $results = \DB::connection('dc')->select($sql);

        $columns = [];
        foreach ($results as $result){
            $columns[] = "`{$result->{'COLUMN_NAME'}}`";
        }

        $columns = implode(',', $columns);

        $cfg = [
            'input' => function () use ($delta, $columns) {
                $sql = <<<SQL
    SELECT {$columns} FROM {$delta} ORDER BY `n_id` LIMIT :limit offset :offset
SQL;
                return new PdoWithLaravel('dc', $sql);
            },
            'output' => function () use ($rebuild) {
                return new MysqlInsertWithPdo(\DB::connection('dc')->getPdo(), $rebuild, null, null, false);
            },
            'before' => function () use ($delta, $rebuild) {
                $idTable = "{$delta}_uuid";
                $sql = <<<SQL
CREATE TABLE {$idTable} SELECT DISTINCT uuid FROM {$delta};
DELETE b FROM {$idTable} a JOIN {$rebuild} b ON a.uuid=b.uuid;
DROP TABLE {$idTable};
SQL;
                \Log::info("MergeSql:" . $sql);
                \DB::connection('dc')->unprepared($sql);
            },
            'fail' => function($etl, $exception){
                throw $exception;
            },
            'limit' => $step,
            'upper' => 10000000
        ];
        $etl = ETL::constructFromCfg($cfg);
        $etl->run();

    }
}