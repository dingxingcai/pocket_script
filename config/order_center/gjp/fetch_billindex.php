<?php

use \App\EtlRunRecord;
use \App\ETL\ETL;
use \App\ETL\Input\PdoWithLaravel;
use \App\ETL\Output\MysqlInsertUpdateWithPdo;
use \App\ETL\Output\CompositeSerially;
use \App\Utility\EtlConstant;

$sql = <<<SQL
select top :limit  *  from 
(select ROW_NUMBER() OVER (ORDER BY b.BillNumberID asc) AS RowNumber,
  b.BillNumberId ,
  b.BillDate ,
  b.BillCode ,
  b.BillType ,
  b.Comment ,
  b.atypeid ,
  b.btypeid ,
  b.etypeid ,
  b.ktypeid ,
  b.ifcheck ,
  b.checke ,
  b.totalmoney ,
  b.totalinmoney ,
  b.totalqty ,
  b.RedWord ,
  b.draft ,
  b.IfStopMoney ,
  b.preferencemoney ,
  b.DTypeId ,
  b.JF ,
  b.VipCardID ,
  b.vipCZMoney ,
  b.jsStyle ,
  b.Poster ,
  b.LastUpdateTime ,
  b.Stypeid ,
  b.checkTime ,
  b.posttime ,
  b.BillTime ,
  b.DealBTypeID ,
  b.NTotalMoney ,
  b.NTotalInMoney ,
  b.NPreferenceMoney ,
  b.NVIPCZMoney 
FROM BillIndex b LEFT JOIN retailbill r 
on r.BillNumberId = b.BillNumberId 
WHERE (b.BillType = 305 or b.BillType = 215) 
and  b.posttime BETWEEN  :timeBegin and :timeEnd) as A  WHERE 
A.RowNumber > (:offset - 1) order by A.RowNumber asc
;
SQL;

$identity = EtlConstant::FETCH_BILLINDEX_ORDER;

return
    [
        'input' => function () use ($sql) {
            return new PdoWithLaravel('gjp', $sql, 1);
        },
        'output' => function () {
            $dc = \DB::connection('dc')->getPdo();

            return new CompositeSerially([
                'billindex' => new MysqlInsertUpdateWithPdo($dc, 'billindex',
                    ['BillNumberId', 'BillDate', 'BillCode', 'BillType', 'Comment', 'atypeid', 'btypeid', 'etypeid', 'ktypeid', 'ifcheck', 'checke', 'totalinmoney', 'totalmoney',
                        'totalqty', 'RedWord', 'draft', 'IfStopMoney', 'preferencemoney', 'DTypeId', 'JF', 'VipCardID', 'vipCZMoney', 'jsStyle', 'Poster', 'LastUpdateTime', 'Stypeid',
                        'checkTime', 'posttime', 'BillTime', 'DealBTypeID', 'NTotalMoney', 'NTotalInMoney', 'NPreferenceMoney', 'NVIPCZMoney'
                    ],
                    ['ifcheck', 'draft', 'RedWord'])
            ], function ($aData) {
                $res = ['billindex' => []];
                foreach ($aData as $data) {
                    $res['billindex'][] = $data;
                }
                return $res;
            });
        },
        'before' => function (ETL $etl) use ($identity) {
            EtlRunRecord::createOrWake(
                $identity,
                $etl,
                function (EtlRunRecord $record = null, EtlRunRecord $lastRecord = null) {
                    $record->params = [
                        'timeBegin' => '2018-01-01 00:00:00',
//                        'timeEnd' => date('Y-m-d H:i:s')
                        'timeEnd' => '2018-05-08 13:40:00'
                    ];
                    $record->marker = 1;

                },
                null
            );
        },
        'after' => function (ETL $etl) use ($identity) {
            EtlRunRecord::endOrSleep($identity, $etl, function (EtlRunRecord $record) {
                $record->marker = 0;

                $record->state = EtlRunRecord::STATE_RUNNING;

                $timeBegin = min(time(), strtotime($record->params['timeEnd']));
                $timeEnd = strtotime('+5 minute', $timeBegin);

                $record->params = [
                    'timeBegin' => date('Y-m-d H:i:s', $timeBegin),
                    'timeEnd' => date('Y-m-d H:i:s', $timeEnd)
                ];
            });
        },
        'fail' => function (ETL $etl, \Exception $e) use ($identity) {
            EtlRunRecord::fail($identity, $etl);
        },
        'limit' => 300,
        'upper' => 300000
    ];