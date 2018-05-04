<?php
namespace App\ETL\Output;

class MysqlInsertWithPdo implements IOutput
{
    use Target;

    protected $pdo = null;
    protected $columns = null;
    protected $handleException = true;

    public function __construct(\PDO $pdo, $tableName, $columns = null, $schedule = null, $handelException=true)
    {
        $this->pdo = $pdo;
        $this->setTarget($tableName);
        $this->columns = $columns;

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);

        $this->handleException = $handelException;
    }

    public function push($data)
    {
        if(count($data) < 1){
            return -1;
        }

        if(!isset($this->columns)){
            $oneData = current($data);
            $this->columns = array_keys($oneData);
        }

        sort($this->columns);
        $columns = $this->columns;
        array_walk($columns, function(&$value){$value =  "`$value`";});

        $insertQueryPrefix = 'insert into ' . $this->target . '(' . implode(',', $columns) . ') values ';

        $insertSqlArray = array();
        foreach($data as $singleData){
            $singleData = array_only($singleData, $this->columns);

            array_walk($singleData, array($this, 'dealValue'));

            ksort($singleData);
            $insertSqlArray[] = '(' . implode(',', $singleData) .')';
        }

        $insertSql = implode(',', $insertSqlArray);
        \Log::info("start insert data to mysql : " . count($insertSqlArray));

        $execSql = $insertQueryPrefix . $insertSql;
        $this->pdo->exec($execSql);
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