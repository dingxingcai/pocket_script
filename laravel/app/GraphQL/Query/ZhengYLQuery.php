<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 15:26
 */

namespace App\GraphQL\Query;

use App\BillIndex;
use App\Library\Helper;
use App\NVipCardSign;
use App\Stock;
use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use GraphQL;
use Rebing\GraphQL\Support\SelectFields;
use DB;


//郑伊露要的数据
class ZhengYLQuery extends Query
{
//    public function authorize(array $args)
//    {
//        return !\Auth::guest();
//    }

    protected $attributes = [
        'name' => 'zhengYL'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('zhenYL'));
    }


    public function args()
    {

    }


    public function resolve($root, $args)
    {

        $totalDays = date('t');                  //当前月总天数
        $date = date('Y-m-1', time());  //每月的开始日期
        $day = date('d', time());        //到目前为止的天数
        $finishedCount = 0;                       //达成率
        $diff = 0;                                //速度对比差值
        $dayTotals = 0;                           //总计当天销售额
        $dayRefundTotals = 0;                    //总计当天总的退货金额
        $totalTotalMoneys = 0;                   //总计当月销售额
        $totalRefundMoneys = 0;                   //总计当月总的退货金额
        $totalTarget = 0;                        //总计月任务
        $totalDiff = 0;


        $datas = [];
        //获取月目标
        $ktypeIds = Helper::getMonthTarGet();
        foreach ($ktypeIds as $ktypeId => $value) {

            //查询仓库名称
            $stock = Stock::select('FullName')->where('typeId', $ktypeId)->first();
            $stock = explode('|', $stock->FullName);
            $info['stock'] = $stock[1];
            //查询仓库的当天销售额
            $dayMoney = DB::connection('sqlsrv')->select("select  sum(TotalMoney) as 'dayMoney'  from billindex
where  BillType = 305 and  KtypeId = '{$ktypeId}'  and RedWord = 0 and  BillDate = CONVERT(varchar(30),getdate(),23);");
            if ($dayMoney[0]->dayMoney) {
                $dayMoney = $dayMoney[0]->dayMoney;
            } else {
                $dayMoney = 0;
            }


            //查询仓库的当天零售退货单
            $dayRegundMoney = DB::connection('sqlsrv')->select("select  sum(TotalMoney) as 'dayMoney'  from billindex
where  BillType = 215 and  KtypeId = '{$ktypeId}' and RedWord = 0   and BillDate = CONVERT(varchar(30),getdate(),23);");
            if ($dayRegundMoney[0]->dayMoney) {
                $dayRegundMoney = $dayRegundMoney[0]->dayMoney;
            } else {
                $dayRegundMoney = 0;
            }
            $dayRefundTotals += $dayRegundMoney;

            $dayTotals += $dayMoney;

            $info['dayMoney'] = round($dayMoney - $dayRegundMoney, 2);


            //获取本月到目前为止的总销售额
            $totalMoney = DB::connection('sqlsrv')->select("select  sum(TotalMoney) as 'totalMoney'  from billindex 
where  BillType = 305  and  KtypeId = '{$ktypeId}' and RedWord = 0   and  BillDate <= CONVERT(varchar(30),getdate(),23)
and BillDate >= '{$date}';");
            if ($totalMoney[0]->totalMoney) {
                $totalMoney = $totalMoney[0]->totalMoney;
            } else {
                $totalMoney = 0;
            }


            //获取本月到目前为止的总零售单退货金额
            $totalRefundMoney = DB::connection('sqlsrv')->select("select  sum(TotalMoney) as 'totalMoney'  from billindex 
where  BillType = 215 and RedWord = 0 and KtypeId = '{$ktypeId}'  and BillDate <= CONVERT(varchar(30),getdate(),23)
and BillDate >= '{$date}';");
            if ($totalRefundMoney[0]->totalMoney) {
                $totalRefundMoney = $totalRefundMoney[0]->totalMoney;
            } else {
                $totalRefundMoney = 0;
            }
            $totalRefundMoneys += $totalRefundMoney;

            $totalTotalMoneys += $totalMoney;
            $info['totalMoney'] = $totalMoney - $totalRefundMoney;
            $info['target'] = $value['money'];
            $info['finishedCount'] = round((($totalMoney - $totalRefundMoney) / $value['money']) * 100, 2) . '%';

            $diff = round($totalMoney - (($value['money'] / $totalDays) * $day), 0);

            $totalDiff += $diff;
            $totalTarget += $value['money'];

            $info['diff'] = $diff;
            $info['title'] = date('Y-m-d H:i:s') . '门店销售统计数据';
            $datas[] = $info;

        }

        $total['stock'] = '合计';
        $total['dayMoney'] = round($dayTotals - $dayRefundTotals, 2);
        $total['totalMoney'] = round($totalTotalMoneys - $totalRefundMoneys, 2);
        $total['target'] = $totalTarget;
        $total['finishedCount'] = round(($totalTotalMoneys / $totalTarget) * 100, 2) . '%';
        $total['diff'] = $totalDiff;
        $datas[] = $total;
        return $datas;
    }
}