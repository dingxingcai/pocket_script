<?php
namespace App\ETL\Output;

class MysqlInsertUpdateWithPdo implements IOutput
{
    use Target;

    protected $pdo = null;
    protected $columns = null;
    protected $updateColumns = null;
    protected $handleException = true;

    public function __construct(\PDO $pdo, $tableName, $columns = null, $updateColumns)
    {
        $this->pdo = $pdo;
        $this->setTarget($tableName);
        $this->columns = $columns;
        $this->updateColumns = $updateColumns;
        if(empty($updateColumns)){
            throw new \Exception("must specified update columns");
        }

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
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

        $updateSqlArray = null;
        foreach ($this->updateColumns as $column){
            $updateSqlArray[] = "`{$column}`=VALUES(`{$column}`)";
        }
        $updateSql = " ON DUPLICATE KEY UPDATE " . implode(',', $updateSqlArray);

        $execSql = $insertQueryPrefix . $insertSql . $updateSql;
        try{
            $this->pdo->exec($execSql);
        }catch (\Exception $e){
            \Log::info("MysqlInsertWithPdoError:{$e->getMessage()} : {$execSql}");
            if(!$this->handleException){
                throw $e;
            };
        }
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