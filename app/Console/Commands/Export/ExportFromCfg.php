<?php

namespace APP\Console\Commands\Export;

use App\ETL\ETL;
use App\ETL\Input\PdoWithLaravel;
use App\ETL\Output\MergedArray;
use App\ETL\Output\XlsxMultiSheet;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Symfony\Component\Console\Input\InputOption;

class ExportFromCfg extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'export:fromCfg';

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
        $cfgKey = $this->option('cfgKey');
        $cfg = \Config::get("export.{$cfgKey}");

        $outputData = array();
        $sqls = $cfg['sqls'];
        foreach($sqls as $key => &$sqlCfg){
            $db = $sqlCfg['database'];
            $sql = $sqlCfg['sql'];
            $step = $sqlCfg['step'];
            $preparationSqls = array_get($sqlCfg,'pre_sql');

            $out = [];
            /**
             * 执行转换-导出数据
             */
            $etlCfg = [
                'input' => function () use ($db, $sql) {
                    return new PdoWithLaravel($db, $sql);
                },
                'output' => function () use (&$out) {
                    return new MergedArray($out);
                },
                'before' => function () use ($db, $preparationSqls) {
                    if ($preparationSqls) {
                        foreach ($preparationSqls as $preSql) {
                            \DB::connection($db)->statement($preSql);
                        }
                    }
                },
                'limit' => $step,
                'upper' => 10000000
            ];
            $etl = ETL::constructFromCfg($etlCfg);
            $etl->run();

            $sqlCfg['data'] = $outputData[$key] = $out;
        }

        $debug = $this->option('debug');
        $debug_to = $this->option('debug_to');
        if($debug == 'true'){
            $cfg['to'] = $debug_to;
            unset($cfg['cc']);
        }

        $subject = $cfg['subject'];
        $content = $cfg['content'];
        $to = $cfg['to'];
        $cc = $cfg['cc'];

        $xlsxPath = $this->option('xlsx_path');
        if (is_null($xlsxPath)) {
            $xlsxPath = storage_path("export/temp/") . date('Ymd') . '/' . $subject . '_' . date('YmdHi') . '.xlsx';
        }

        $xlsx = new XlsxMultiSheet($xlsxPath);
        $xlsx->push($outputData);

        $tplName = 'emails.export.' . array_get($cfg, 'mail_tpl');
        \Mail::send(array('html' => $tplName),
            array('content' => $content, 'sqls' => $sqls, 'previewCount' => $cfg['preview_count']), function (Message $message)
            use ($xlsxPath, $subject, $cc, $to) {
                is_array($cc) && $message->cc($cc);
                $message->to($to)->subject($subject)->attach($xlsxPath);
            });
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('cfgKey', null, InputOption::VALUE_REQUIRED, '执行指定cfg中的导出规则', null),
            array('xlsx_path', null, InputOption::VALUE_OPTIONAL, 'excel存储路径'),
            array('debug', null, InputOption::VALUE_OPTIONAL, '调试模式下,会发给指定人', false),
            array('debug_to', null, InputOption::VALUE_OPTIONAL, '调试模式下,指定人邮箱', 'xiongmeng@pocketnoir.com'),
        );
    }
}
