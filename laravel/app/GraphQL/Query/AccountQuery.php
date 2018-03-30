<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 18:09
 */

namespace App\GraphQL\Query;

use App\AcItems;
use App\User;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use App\Library\Helper;

class AccountQuery extends Query
{

    protected $attributes = [
        'name' => 'account'
    ];

    public function type()
    {

        return Type::listOf(GraphQL::type('account'));
    }


    public function args()
    {
        return [
            'usercode' => ['name' => 'usercode', Type::string()],
            'name' => ['name' => 'name', Type::string()],
        ];
    }

    public function resolve($root, $args)
    {
        $posInfo = Helper::posInfo();

        $nids = [
            $posInfo->BankCardID,
            $posInfo->CashID,
            $posInfo->alipayid,
            $posInfo->wxpayid
        ];

        $acItems = AcItems::whereIn('nId', $nids)->get();

        return $acItems;

    }


}