<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 15:26
 */

namespace App\GraphQL\Query;

use App\BillIndex;
use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use GraphQL;
use Rebing\GraphQL\Support\SelectFields;
use DB;


//自动推送数据图片
class TotalOrderQuery extends Query
{
//    public function authorize(array $args)
//    {
//        return !\Auth::guest();
//    }

    protected $attributes = [
        'name' => 'totalOrder'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('totalOrder'));
    }


    public function args()
    {

    }


    public function resolve($root, $args)
    {


        //七天总订单
        $totalOrder = DB::connection('sqlsrv')->select("select  CONVERT(varchar(10), BillDate, 23) as 'date' ,count(*) as 'totalOrders',
SUM( CASE WHEN VipCardID!=-1 THEN 1 ELSE 0 END) as 'vipOrders' ,
SUM( CASE WHEN VipCardID=-1 THEN 1 ELSE 0 END) as 'notVipOrders'  from
 BillIndex  where billtype = 305 and  datediff(dd,BillDate,getdate()) <= 7
and BillDate < CONVERT(varchar(30),getdate(),23) and RedWord=0   group by BillDate order by billdate desc;");

        return $totalOrder;
    }
}