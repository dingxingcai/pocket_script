<?php

namespace APP\Console\Commands\Export;

use App\ETL\ETL;
use App\ETL\Input\PdoWithLaravel;
use App\ETL\Output\XlsxSingleSheet;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Symfony\Component\Console\Input\InputOption;

class ExportCustom extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'export:custom';

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
        $sql = $this->option('sql');
        $db = $this->option('db');
        $step = $this->option('step');
        $subject = $this->option('subject');
        $content = $this->option('content');
        $previewCount = $this->option('preview_count');
        $preparationSql = $this->option('pre_sql');

        $to = explode(',', $this->option('to'));
        $cc = $this->option('cc');
        !is_null($cc) && $cc = explode(',', $cc);

        $xlsxPath = $this->option('xlsx_path');
        if (is_null($xlsxPath)) {
            $xlsxPath = storage_path("export/temp/$db/") . date('Ymd') . '/' . $subject . '_' . date('YmdHi') . '.xlsx';
        }

        $outputData = array();

        /**
         * 执行转换-导出数据
         */
        $cfg = [
            'input' => function () use ($db, $sql) {
                return new PdoWithLaravel($db, $sql);
            },
            'output' => function () use ($xlsxPath) {
                return new XlsxSingleSheet($xlsxPath);
            },
            'before' => function () use ($db, $preparationSql) {
                if ($preparationSql) {
                    $sqls = explode(';', $preparationSql);
                    foreach ($sqls as $preSql) {
                        if ($preSql) {
                            \DB::connection($db)->statement($preSql);
                        }
                    }
                }
            },
            'beforePush' => function($etl, $aData) use(&$outputData){
                foreach ($aData as $data){
                    $outputData[] = $data;
                }
            },
            'limit' => $step,
            'upper' => 10000000
        ];
        $etl = ETL::constructFromCfg($cfg);
        $etl->run();

        /**
         * 发送邮件
         */
        $mailTpl = $this->option('mail_tpl');
        if (in_array($mailTpl, array('default', 'table_and_excel'))) {
            $tplName = 'emails.export.' . $mailTpl;
        } else {
            $tplName = 'emails.export.default';
        }
        \Mail::send(array('html' => $tplName),
            array('content' => $content, 'previewCount' => $previewCount, 'sqls' => array(
                $subject => array('database' => $db, 'sql' => $sql, 'data' => $outputData))
            ), function (Message $message)
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
            array('sql', null, InputOption::VALUE_REQUIRED, '需要执行的sql'),
            array('subject', null, InputOption::VALUE_REQUIRED, '邮件标题'),
            array('to', null, InputOption::VALUE_REQUIRED, '邮件收件人'),
            array('db', null, InputOption::VALUE_REQUIRED, '数据库连接地址-参考配置文件'),

            array('content', null, InputOption::VALUE_OPTIONAL, '正文', 'Done.'),
            array('cc', null, InputOption::VALUE_OPTIONAL, ''),
            array('preview_count', null, InputOption::VALUE_OPTIONAL, '', 100),
            array('step', null, InputOption::VALUE_OPTIONAL, '数量太多时,一次从sql取的数据', 10000),
//            array('from', null, InputOption::VALUE_OPTIONAL, ''),
            array('xlsx_path', null, InputOption::VALUE_OPTIONAL, 'excel存储路径'),
            array('pre_sql', null, InputOption::VALUE_OPTIONAL, '前置sql操作-执行转换前需要执行的sql，比如mysql中设置变量', null),
            array('mail_tpl', null, InputOption::VALUE_OPTIONAL, '邮件模板-参考代码中的配置内容', 'default'),
        );
    }
}
