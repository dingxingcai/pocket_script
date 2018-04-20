<?php
namespace App\ETL\Input;

class PdoWithLaravel implements IInput
{
    use Marker;

    protected $connection = null;
    protected $sql = '';

    public function __construct($connection, $sql, $offset=0)
    {
        $this->connection = $connection;
        $this->sql = $sql;
        $this->setMarker(intval($offset));
    }

    public function pull($limit, $params)
    {
        $params['limit'] = $limit;
        $params['offset'] = $this->marker;

        $result = \DB::connection($this->connection)->select($this->sql, $params);

        array_walk($result, function (&$value){
            $value = (array)$value;
        });

        $this->marker += count($result);
        return $result;
    }
}