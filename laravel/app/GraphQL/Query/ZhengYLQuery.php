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
        return Type::listOf(GraphQL::type('return'));
    }


    public function args()
    {

    }


    public function resolve($root, $args)
    {


    }
}