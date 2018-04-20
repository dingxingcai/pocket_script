<?php

namespace App\ETL\Staging;

class SingleTableMysqlWithPdo
{
    protected $dbConnection = null;
    protected $tableName = null;

    protected $stagingTable = null;
    protected $swapTable = null;
    protected $originalTable = null;
    protected $schedule = null;
    protected $postfix = null;
    protected $mark = null;

    public function __construct($dbConnection, $tableName, $schedule, $postfix, $mark = "%table%")
    {
        $this->postfix = $postfix;
        $this->dbConnection = $dbConnection;

        $this->tableName = $this->originalTable = $tableName;
        $this->stagingTable = $tableName . $postfix;
        $this->swapTable = $tableName . "_swap";
        $this->schedule = $schedule;

        $this->mark = $mark;
    }

    public function getStaging()
    {
        return $this->stagingTable;
    }

    public function getOriginal()
    {
        return $this->originalTable;
    }

    public function staging()
    {
        if (is_null($this->schedule)) {
            throw new \Exception("需要传入schedule才能使用staging！");
        }
        $sql = str_replace($this->mark, $this->stagingTable, $this->schedule);
        \DB::connection($this->dbConnection)->unprepared($sql);

        $this->tableName = $this->stagingTable;
    }

    public function swap()
    {
        $db = \DB::connection($this->dbConnection);

        \Schema::setConnection($db);

        if (\Schema::hasTable($this->originalTable)) {
            $sql = <<<SQL
        RENAME TABLE {$this->originalTable} TO {$this->swapTable};
        RENAME TABLE {$this->stagingTable} TO {$this->originalTable};
        RENAME TABLE {$this->swapTable} TO {$this->stagingTable};
SQL;
        } else {
            $sql = <<<SQL
        RENAME TABLE {$this->stagingTable} TO {$this->originalTable};
SQL;
        }

        $db->unprepared($sql);
    }

    public function clean()
    {
        $db = \DB::connection($this->dbConnection);
        $sql = "DROP TABLE IF EXISTS {$this->stagingTable}";
        \Log::info("clean : {$sql}");
        $db->unprepared($sql);
    }
}