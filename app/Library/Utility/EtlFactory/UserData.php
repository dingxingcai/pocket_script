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
use App\Jobs\LoadUserDataFromSAEStorageToRedisQueue;
use App\Utility\EtlConstant;
use App\Utility\EtlParser;
use App\Utility\EtlSchema;
use Illuminate\Console\Scheduling\Schedule;

class UserData
{
    private $delta = false;
    private $identity = false;

    public function __construct($delta)
    {
        $this->delta = $delta;

        $this->identity = $delta ? EtlConstant::DELTA_PARSE_USER_DATA_FROM_STORAGE : EtlConstant::REBUILD_PARSE_USER_DATA_FROM_STORAGE;
    }

    public static $outMaps = [
        EtlSchema::TABLE_BACKUP => [],
        EtlSchema::TABLE_STATIONS => [],
        EtlSchema::TABLE_EXPENSE_TYPES => [],
        EtlSchema::TABLE_USER_CAR_RELATIONS => []
    ];

    public static function schedule(Schedule $schedule)
    {
        $schedule->command(
            ETLRunner::class,
            ['--queue=etl.parse_user_data.delta']
        )->cron("/10 * * * *")->withoutOverlapping()->runInBackground()->when(function (){
            return \Cache::store('database')->has(EtlConstant::DELTA_PARSE_USER_DATA_FROM_STORAGE)
                && !EtlRunRecord::isRunning(EtlConstant::REBUILD_PARSE_USER_DATA_FROM_STORAGE);
        });

        $schedule->call(function (){
            \Cache::store('database')->forever(EtlConstant::DELTA_PARSE_USER_DATA_FROM_STORAGE, '1');
        })->name("start_" . EtlConstant::DELTA_PARSE_USER_DATA_FROM_STORAGE)->cron("31 4 * * *")->withoutOverlapping()->runInBackground();

        $schedule->call(function (){
            self::mergeOneEndedRecord();
        })->name(EtlConstant::MERGE_PARSE_USER_DATA_FROM_STORAGE )->cron("31 8 * * *")->withoutOverlapping()->runInBackground();

        $schedule->command(
            ETLRunner::class,
            ['--queue=etl.parse_user_data.rebuild']
        )->cron("/5 8-23 * * *")->withoutOverlapping()->runInBackground()->when(function (){
            return \Cache::store('database')->has(EtlConstant::REBUILD_PARSE_USER_DATA_FROM_STORAGE)
                && !EtlRunRecord::isRunning(EtlConstant::DELTA_PARSE_USER_DATA_FROM_STORAGE);
        });

        $schedule->command(
            ETLRecordClean::class,
            ['--identity=' . EtlConstant::DELTA_PARSE_USER_DATA_FROM_STORAGE, '--view=false', '--state=merged', '--before=1440']
        )->cron("41 10 * * *")->withoutOverlapping()->runInBackground();
    }

    public function input($prefix = 'UserData', $step = 1000)
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
                5,
                function ($data) {
                    return (
                    new LoadUserDataFromSAEStorageToRedisQueue(
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
                $outs[$table] = new MysqlInsertWithPdo($dc, $table);
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
        $deltaRecord = EtlRunRecord::fetchOneEnd(EtlConstant::DELTA_PARSE_USER_DATA_FROM_STORAGE);
        if(empty($deltaRecord)){
            return;
        }
        /** @var MultiTableWithMysqlPdo $stage */
        $stage = $deltaRecord->stage;

        $postfix = '_delta';

        foreach (array_keys(self::$outMaps) as $rebuild){
            $stageKey = $rebuild . $postfix;
            $delta = $stage->stage($stageKey)->getStaging();

            if($rebuild == EtlSchema::TABLE_BACKUP){
                self::mergeForBackup($delta, $rebuild);
            }else{
                self::mergeOneTable($delta, $rebuild);
            }
        }

        $deltaRecord->state = EtlRunRecord::STATE_MERGED;

        $deltaRecord->save();
    }

    public static function mergeForBackup($delta, $rebuild, $step = 5000)
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
                return new MysqlInsertWithPdo(\DB::connection('dc')->getPdo(), $rebuild,null, null, false);
            },
            'before' => function () use ($delta, $rebuild) {
                $sql = <<<SQL
DELETE a FROM {$delta} a JOIN {$rebuild} b ON a.userId=b.userId AND a.backupVersion = b.backupVersion;
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
                $idTable = "{$delta}_userId";
                $sql = <<<SQL
CREATE TABLE {$idTable} SELECT DISTINCT userId FROM {$delta};
DELETE b FROM {$idTable} a JOIN {$rebuild} b ON a.userId=b.userId;
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