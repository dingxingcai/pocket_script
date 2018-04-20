<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use DB;

class Upload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:Upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $employees = DB::connection('sqlsrv')->table('employee')->select('typeId', 'UserCode', 'FullName')->get();
        foreach ($employees as $employee) {
            $user = new User();
            $user->uid = $employee->typeId;
            $user->usercode = $employee->UserCode;
            $user->name = $employee->FullName;
            $user->loginat = date('Y-m-d H:i:s', time());
            $user->telephone = "";
            $user->password = md5($employee->typeId . 'uid' . '123456');   //初始化的密码都是123456
            $user->save();
        }
        echo 'success';

    }
}
