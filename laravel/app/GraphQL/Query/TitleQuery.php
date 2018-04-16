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
class TitleQuery extends Query
{
//    public function authorize(array $args)
//    {
//        return !\Auth::guest();
//    }

    protected $attributes = [
        'name' => 'title'
    ];

    public function type()
    {
        return Type::listOf(GraphqL::type('title'));
    }


    public function args()
    {

    }


    public function resolve($root, $args)
    {
        $names = [];
        $infos = [
            'item1' => '门店',
            'item2' => '当天营业额(元)',
            'item3' => date('m', time()) . '月营业额(元)',
            'item4' => date('m', time()) . '月任务(元)',
            'item5' => '当前时间达成率',
            'item6' => '进度对比营业额',
        ];
        foreach ($infos as $item => $value) {
            $info['name'] = $value;
            $names[] = $info;
        }

        return $names;
    }
}