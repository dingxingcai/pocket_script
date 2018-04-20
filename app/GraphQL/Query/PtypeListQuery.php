<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/23
 * Time: 15:26
 */

namespace App\GraphQL\Query;

use App\Library\Helper;
use DB;
use App\Ptype;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use GraphQL;


/*
 * 商品列表查询搜索
 * */

class PtypeListQuery extends Query
{

    public function authorize(array $args)
    {
        return !\Auth::guest();
    }

    protected $attributes = [
        'name' => 'ptypeListQuery'
    ];

    public function type()
    {
        return GraphQL::paginate('ptype');
    }

    public function args()
    {
        return [
            'UserCode' => ['name' => 'etypeid', Type::string()],
            'FullName' => ['name' => 'FullName', Type::string()],
            'EntryCode' => ['name' => 'EntryCode', Type::string()],
            'page' => ['name' => 'page', Type::int()],
            'limit' => ['name' => 'limit', Type::int()],
            'search' => ['name' => 'search', Type::string()]
        ];
    }


    public function resolve($root, $args)
    {


        $posInfo = Helper::posInfo();

        $query = DB::connection('sqlsrv')->table('ptype')
            ->join('Ptype_Price', 'ptype.typeId', '=', 'Ptype_Price.PTypeID')
            ->join('GoodsStocks', 'GoodsStocks.PtypeId', '=', 'ptype.typeId')
            ->select('ptype.typeId', 'ptype.UserCode', 'ptype.FullName', 'ptype.Standard', 'ptype.Area', 'ptype.EntryCode', 'ptype.CreateDate', 'Ptype_Price.RetailPrice', 'GoodsStocks.Qty')
            ->where('GoodsStocks.KtypeId', $posInfo->ktypeid)
            ->where('Ptype_Price.RetailPrice', '>', 0);
        if (isset($args['search']) && !empty($args['search'])) {
            $query->where(function ($query) use ($args) {
                $query->orWhere('Ptype.FullName', 'like', '%' . $args['search'] . '%')
                    ->orWhere('Ptype.UserCode', 'like', '%' . $args['search'] . '%')
                    ->orWhere('Ptype.EntryCode', 'like', '%' . $args['search'] . '%')
                    ->orWhere('Ptype.Standard', 'like', '%' . $args['search'] . '%');
            });

        }
        $ptypes = $query->orderBy('GoodsStocks.Qty', 'desc')->paginate($args['limit'], ['*'], 'page', $args['page']);

        return $ptypes;

    }
}