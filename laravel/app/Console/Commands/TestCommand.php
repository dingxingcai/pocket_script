<?php

namespace App\Console\Commands;

use App\Employee;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TestCommand';

    /**
     * The console command descphp aription.
     *
     * @var string
     */
    protected $description = 'TestCommand';

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
        $employee = Employee::select()->where('typeid', '00000')->get();
        echo $employee;
    }
}
