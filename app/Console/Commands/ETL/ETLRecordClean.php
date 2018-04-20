<?php

namespace App\Console\Commands\ETL;

use App\EtlRunRecord;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class ETLRecordClean extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etl:clean';

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
        $before = $this->option('before');
        $date = date('Y-m-d H:i:s', strtotime("-{$before} minute"));
        $view = $this->option('view');
        $identity = $this->option('identity');
        $state = $this->option('state');

        if (!in_array($state, array(
            EtlRunRecord::STATE_CANCEL,
            EtlRunRecord::STATE_MERGED))) {
            throw new \Exception("仅支持失败或结束的状态");
        }

        $records = EtlRunRecord::whereIdentity($identity)
            ->where('state', '=', $state)
            ->where('is_cleaned', '=', 0)
            ->where('ts_created', '<', $date)->get();

        foreach ($records as $record) {
            $stage = $record->stage;
            $cleanMethods = 'clean';
            if (method_exists($stage, $cleanMethods)) {
                if ($view == 'true') {
                    \Log::info("stage view : " . print_r($stage, true));
                } else {
                    $stage->$cleanMethods();
                    $record->is_cleaned = 1;
                    $record->save();
                }
            } else {
                \Log::info("指定record所对应的stage不存在clean方法");
            }
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('identity', null, InputOption::VALUE_OPTIONAL, 'the identity to clean'),
            array('before', null, InputOption::VALUE_OPTIONAL, '删除指定间隔前的（以分为单位）', 172800),
            array('view', null, InputOption::VALUE_OPTIONAL, '预览模式', true),
            array('state', null, InputOption::VALUE_OPTIONAL, '删除指定状态的', 'fail'),
        );
    }
}
