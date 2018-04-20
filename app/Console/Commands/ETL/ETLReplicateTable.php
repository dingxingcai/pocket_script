<?php

namespace APP\Console\Commands\ETL;

use App\ETL\ETL;
use App\ETL\Input\PdoWithLaravel;
use App\ETL\Output\MysqlInsertWithPdo;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class ETLReplicateTable extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etl:replicate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $table = $this->option('table');
        $sort = $this->option('sort');
        $columns = $this->option('columns');

        $sourceDB = $this->option('source');
        $targetDB = $this->option('target');
        $prefix = $this->option('prefix');
        $step = $this->option('step');

        \DB::connection($targetDB)->unprepared('SET FOREIGN_KEY_CHECKS=0;');

        $targetTable = $prefix . $table;
        $cfg = [
            'input' => function () use ($table, $columns, $sort, $sourceDB) {
                $sql = <<<SQL
    SELECT {$columns} FROM {$table} ORDER BY {$sort} LIMIT :limit offset :offset
SQL;
                return new PdoWithLaravel($sourceDB, $sql);
            },
            'output' => function () use ($targetDB, $targetTable) {
                return new MysqlInsertWithPdo(\DB::connection($targetDB)->getPdo(), $targetTable);
            },
            'before' => function () use ($sourceDB, $table, $targetTable, $targetDB) {
                $result = \DB::connection($sourceDB)->select("SHOW CREATE TABLE {$table}");
                if (count($result) > 0) {
                    $createSql = $result[0]->{'Create Table'};
                    \DB::connection($targetDB)->unprepared("SET sql_mode=''");
                    \DB::connection($targetDB)->unprepared("DROP TABLE IF EXISTS " . $targetTable);

                    $createSql = str_replace("`{$table}`", "`{$targetTable}`", $createSql);
                    \DB::connection($targetDB)->unprepared($createSql);
                }
            },
            'limit' => $step,
            'upper' => 10000000
        ];
        $etl = ETL::constructFromCfg($cfg);
        $etl->run();

        \DB::connection($targetDB)->unprepared('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('table', null, InputOption::VALUE_REQUIRED, 'the table to replicate'),
            array('sort', null, InputOption::VALUE_REQUIRED, '排序的字符串', ''),
            array('source', null, InputOption::VALUE_OPTIONAL, 'source database', 'business'),
            array('target', null, InputOption::VALUE_OPTIONAL, 'target database', 'dc'),
            array('step', null, InputOption::VALUE_OPTIONAL, 'the step', 1000),
            array('columns', null, InputOption::VALUE_OPTIONAL, '需要迁移的数据列', '*'),
            array('prefix', null, InputOption::VALUE_OPTIONAL, '新表的名称', 'odsb_'),
        );
    }
}
