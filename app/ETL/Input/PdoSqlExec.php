<?php
namespace App\ETL\Input;

class PdoSqlExec implements IInput
{
    protected $sql = null;
    protected $pdo = null;
    public function __construct(\PDO $pdo,$sql)
    {
        $this->pdo = $pdo;
        $this->sql = $sql;
    }

    public function pull($limit, $params)
    {
        $this->pdo->exec($this->sql);
        return [];
    }

    public function getOffset()
    {
        // TODO: Implement getOffset() method.
    }

    public function setOffset($offset)
    {
        // TODO: Implement setOffset() method.
    }
}