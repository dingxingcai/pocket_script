<?php
namespace App\ETL\Output;

use Illuminate\Database\Connection;

class MysqlUpdate implements IOutput
{
    protected $connection = null;
    protected $tableName = null;
    protected $primaryKey = 'id';

    public function __construct(Connection $connection, $tableName, $primaryKey = 'id')
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->primaryKey = $primaryKey;
    }

    public function push($updateData)
    {
        if(count($updateData) < 1){
            return -1;
        }

        $updateSqlArray = array();
        foreach($updateData as $singleData){
            if(empty($singleData[$this->primaryKey])){
                continue;
            }
            $dataStr = array();
            foreach($singleData as $key => $value){
                $this->dealValue($value);
                $dataStr[] = sprintf("`%s`=%s", $key, $value);
            }

            $updateSqlArray[] = sprintf("UPDATE %s SET %s WHERE %s=%s",
                $this->tableName,
                implode($dataStr, ','),
                $this->primaryKey,
                $singleData[$this->primaryKey]
            );
        }

        if(count($updateSqlArray) > 0){
            $updateSql = implode(';', $updateSqlArray);

            \Log::info("start update data to mysql : " . count($updateSqlArray));

            $this->connection->unprepared($updateSql);
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