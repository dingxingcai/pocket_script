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
class DayOrderQuery extends Query
{
//    public function authorize(array $args)
//    {
//        return !\Auth::guest();
//    }

    protected $attributes = [
        'name' => 'dayOrder'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('dayOrder'));
    }


    public function args()
    {

    }


    public function resolve($root, $args)
    {

        //每天各门店详细订单
        $dayOrder = DB::connection('sqlsrv')->select("select  CONVERT(varchar(10), b.billdate, 23) as 'date' ,s.FullName as 'storeName',isnull(count(billdate) ,0) as  'totalOrders', SUM( CASE WHEN b.VipCardID!=-1 THEN 1 ELSE 0 END)  as 'vipOrders' ,SUM( CASE WHEN b.VipCardID=-1 THEN 1 ELSE 0 END)   as 'notVipOrders' from BillIndex b left join stock s on s.typeId = b.ktypeid where datediff(dd,BillDate,getdate()) <= 1  and BillType = 305 and  BillDate < CONVERT(varchar(30),getdate(),23)  and b.RedWord=0  group by BillDate,s.FullName  order by BillDate desc ,'totalOrders' DESC  ; ");

        return $dayOrder;
    }
}