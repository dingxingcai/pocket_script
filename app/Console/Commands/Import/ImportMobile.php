<?php
namespace APP\Console\Commands\Import;

use App\ETL\Output\MysqlInsertUpdateWithPdo;
use App\Jobs\RefreshMobile;
use App\Library\CurlWrapper;
use Illuminate\Console\Command;

/**
 * Class ExportForHeavyBuyer
 */
class ImportMobile extends Command
{

    private $_serverName = '';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'import:mobile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导入手机号';

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
//        $mobile = '18611367408';
//        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
////        $result = $reader->load(__DIR__ . '/test.xlsx');
//        $result = $reader->load(__DIR__ . '/all.xlsx');
//
//        foreach ($result->getAllSheets() as $sheet){
//            $data = $sheet->toArray();
//            foreach ($data as $mobile){
//                if(!empty($mobile[0])){
////                    \DB::table('mobile_dc_20180427')->insert(['mobile' => $mobile]);
//                    \DB::statement("INSERT INTO mobile_dc_20180427 (mobile) VALUES ({$mobile[0]}) ON DUPLICATE KEY UPDATE mobile=VALUES(mobile);");
//                }
//            }
//
//        }
//
        $writer = new MysqlInsertUpdateWithPdo(\DB::connection('dc')->getPdo(),
            'mobile_dc_20180427',
            ['mobile','areaCode','num','name','postCode','provCode','prov','cityCode','type','city', 'ret_code', 'remark'],
            ['areaCode','num','name','postCode','provCode','prov','cityCode','type','city', 'ret_code', 'remark']
        );


        $mobile = '18611367408';

        \DB::table('mobile_dc_20180427')->where('provCode', '<', 1)->where('ret_code', '<>', '-1')->orderBy('mobile')->chunk(50000,
            function ($items, $b)use($writer){
            foreach ($items as $irem) {
                $mobile = $irem->mobile;
                dispatch(new RefreshMobile($mobile))->onQueue('refresh')->onConnection('redis');

//                $headers = ["Authorization:APPCODE " . 'cf62d9ad41674e9daaa615bbedbf5f85'];
//                $res = CurlWrapper::get(
//                    ['num' => $mobile],
//                    'https://ali-mobile.showapi.com/6-1',
//                    30,
//                    $headers
//                );
//
//                $a = $res;
//                $aa = json_decode($a, true);
//
//
//                $insert = $aa['showapi_res_body'];
////            $insert['mobile'] = $mobile;
////            $writer->push([$insert]);
//                \DB::table('mobile_dc_20180427')->where('mobile', '=', $mobile)->update($insert);
            }
            exit;
        });
    }


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(//			array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
//          array('openapi_host', null, InputOption::VALUE_REQUIRED, '', null),
//          array('flag', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}
