<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 15:26
 */

namespace App\GraphQL\Query;

use App\BillIndex;
use App\NVipCardSign;
use App\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use GraphQL;
use Rebing\GraphQL\Support\SelectFields;
use DB;


//自动推送数据图片
class VipQuery extends Query
{
//    public function authorize(array $args)
//    {
//        return !\Auth::guest();
//    }

    protected $attributes = [
        'name' => 'autoPost'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('vip'));
    }


    public function args()
    {

    }


    public function resolve($root, $args)
    {
        //七天新增的总会员数量
        $vips = DB::connection('sqlsrv')->select("select top 7 CONVERT(varchar(10), CreateDate, 23) as 'date', count(*) as 'vipNums' from nVipCardSign where createDate < CONVERT(varchar(30),getdate(),23)  GROUP BY CreateDate order by CreateDate desc;");

        $total = NVipCardSign::select('VipCardID')->count();
        $info = [
            'date' => '总计会员数',
            'vipNums' => $total
        ];
        $vips[] = $info;

        return $vips;
    }
}