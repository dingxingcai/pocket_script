<?php

namespace App\ETL\Suite;

use App\ETL\Input\PdoSqlExec;
use App\ETL\Staging\SingleTableMysqlWithPdo;

class PdoSqlExecWithStaging
{
    public function create($connection, $table, $schema, $execSql)
    {
        $stage = new SingleTableMysqlWithPdo($connection, $table, $schema, date("YmdHis"));
        return [
            'input' => function() use ($connection, $table, $execSql, $stage){
                $target = $stage->getStaging();
                $original = $stage->getOriginal();

                $sql = str_replace("`{$original}`", "`{$target}`", $execSql);

                return new PdoSqlExec(\DB::connection($connection)->getPdo(), $sql);
            },
            'before' => function() use ($stage){
                $stage->staging();
            },
            'after' => function() use ($stage){
                $stage->swap();
                $stage->clean();
            },
            'limit' => 1,
            'upper' => 1
        ];
    }
}
