<?php
namespace App\ETL\Staging;

class MultiTableWithMysqlPdo
{
    /** @var SingleTableMysqlWithPdo[] */
    protected $maps = [];

    public function __construct($dbConnection, $tableScheduleMap, $postfix)
    {
        foreach ($tableScheduleMap as $table => $schedule){
            $this->maps[$table] = new SingleTableMysqlWithPdo($dbConnection, $table, $schedule, $postfix);
        }
    }

    public function stage($table)
    {
        return $this->maps[$table];
    }

    public function staging()
    {
        foreach ($this->maps as $map){
            $map->staging();
        }
    }

    public function swap()
    {
        foreach ($this->maps as $map){
            $map->swap();
        }
    }

    public function clean()
    {
        foreach ($this->maps as $map){
            $map->clean();
        }
    }
}