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
use App\Ptype;
use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use GraphQL;
use Rebing\GraphQL\Support\SelectFields;
use DB;


//各品牌的销售额占比
class BrandQuery extends Query
{
//    public function authorize(array $args)
//    {
//        return !\Auth::guest();
//    }

    protected $attributes = [
        'name' => 'brand'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('brand'));
    }


    public function args()
    {

    }


    public function resolve($root, $args)
    {

        //统计销售额
        $brands = DB::connection('sqlsrv')->select("select CONVERT(varchar(10), b.billdate, 23) as 'date', p.ParID,sum(r.total) as  'money' 
from billindex b left join retailBill r on b.BillNumberID = r.BillNumberID inner join ptype p on p.typeId = r.PtypeId 
where datediff(dd,b.BillDate,getdate()) <= 1  and b.BillType = 305 and  b.BillDate < CONVERT(varchar(30),getdate(),23)  group by p.ParID,b.BillDate;");

        //统计总计的销售额
        $totalMoney = DB::connection('sqlsrv')->select("select  sum(TotalMoney) as 'totalMoney'  from billindex b 
where datediff(dd,b.BillDate,getdate()) <= 1  and b.BillType = 305 and  b.BillDate < CONVERT(varchar(30),getdate(),23);");

        foreach ($brands as &$brand) {
            $ptype = Ptype::select('FullName')->where('typeId', $brand->ParID)->first();
            $brand->name = $ptype->FullName;
            $brand->count = Helper::getNum($brand->money, $totalMoney[0]->totalMoney);

        }

        return $brands;
    }
}