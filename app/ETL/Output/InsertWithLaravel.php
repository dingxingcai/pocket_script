<?php
namespace App\ETL\Output;

use Illuminate\Support\Facades\DB;

class InsertWithLaravel implements IOutput
{
    protected $connection = null;
    protected $tableName = null;
    protected $columns = null;
    protected $disposer = null;

    public function __construct($connection, $tableName, $columns = null,\Closure $disposer=null)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->columns = $columns;
        $this->disposer = $disposer;
    }

    public function push($data)
    {
        if(count($data) < 1){
            return -1;
        }

        if(!is_null($this->disposer)){
            $data = call_user_func($this->disposer, $data);
        }

        \Log::info("start insert data to mysql : " . count($data));

        DB::connection($this->connection)->table($this->tableName)->insert($data);
    }

    protected function dealValue(&$value)
    {
        if(is_null($value)) {
            $value = 'null';
        } else {
            $value= '\'' . addslashes($value) . '\'';
        }
    }
}