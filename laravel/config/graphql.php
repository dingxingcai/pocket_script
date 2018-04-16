<?php


use example\Mutation\ExampleMutation;
use example\Query\ExampleQuery;
use example\Type\ExampleRelationType;
use example\Type\ExampleType;

return [

    // The prefix for routes
    'prefix' => 'graphql',

    // The routes to make GraphQL request. Either a string that will apply
    // to both query and mutation or an array containing the key 'query' and/or
    // 'mutation' with the according Route
    //
    // Example:
    //
    // Same route for both query and mutation
    //
    // 'routes' => 'path/to/query/{graphql_schema?}',
    //
    // or define each route
    //
    // 'routes' => [
    //     'query' => 'query/{graphql_schema?}',
    //     'mutation' => 'mutation/{graphql_schema?}',
    // ]
    //
    'routes' => '{graphql_schema?}',

    // The controller to use in GraphQL request. Either a string that will apply
    // to both query and mutation or an array containing the key 'query' and/or
    // 'mutation' with the according Controller and method
    //
    // Example:
    //
    // 'controllers' => [
    //     'query' => '\Rebing\GraphQL\GraphQLController@query',
    //     'mutation' => '\Rebing\GraphQL\GraphQLController@mutation'
    // ]
    //
    'controllers' => \Rebing\GraphQL\GraphQLController::class . '@query',

    // Any middleware for the graphql route group
    'middleware' => [],

    // The name of the default schema used when no argument is provided
    // to GraphQL::schema() or when the route is used without the graphql_schema
    // parameter.
    'default_schema' => 'default',

    // The schemas for query and/or mutation. It expects an array of schemas to provide
    // both the 'query' fields and the 'mutation' fields.
    //
    // You can also provide a middleware that will only apply to the given schema
    //
    // Example:
    //
    //  'schema' => 'default',
    //
    //  'schemas' => [
    //      'default' => [
    //          'query' => [
    //              'users' => 'App\GraphQL\Query\UsersQuery'
    //          ],
    //          'mutation' => [
    //
    //          ]
    //      ],
    //      'user' => [
    //          'query' => [
    //              'profile' => 'App\GraphQL\Query\ProfileQuery'
    //          ],
    //          'mutation' => [
    //
    //          ],
    //          'middleware' => ['auth'],
    //      ],
    //      'user/me' => [
    //          'query' => [
    //              'profile' => 'App\GraphQL\Query\MyProfileQuery'
    //          ],
    //          'mutation' => [
    //
    //          ],
    //          'middleware' => ['auth'],
    //      ],
    //  ]
    //
    'schemas' => [
        'default' => [
            'query' => [
                'user' => App\GraphQL\Query\UsersQuery::class,
                'userInfo' => App\GraphQL\Query\UsersInfoQuery::class,
                'employee' => App\GraphQL\Query\EmployeeQuery::class,
                'billIndex' => App\GraphQL\Query\BillIndexQuery::class,
                'retailBill' => App\GraphQL\Query\RetailBillQuery::class,
                'nVipCardSign' => App\GraphQL\Query\NVipCardSignQuery::class,
                'billIndexOrderQuery' => App\GraphQL\Query\BillIndexOrderQuery::class,
//                'billIndexOrderQuery1' => App\GraphQL\Query\BillIndexOrderQuery1::class,
                'goodsStock' => App\GraphQL\Query\GoodsStockQuery::class,
                'account' => App\GraphQL\Query\AccountQuery::class,
                'test' => App\GraphQL\Query\TestQuery::class,
                'discountMoney' => App\GraphQL\Query\discountMoneyQuery::class,
                'ptypeListQuery' => App\GraphQL\Query\PtypeListQuery::class,
                'vip' => App\GraphQL\Query\VipQuery::class,
                'dayOrder' => App\GraphQL\Query\DayOrderQuery::class,
                'totalOrder' => App\GraphQL\Query\TotalOrderQuery::class,
                'brand' => App\GraphQL\Query\BrandQuery::class,  //饼状销售额占比（前一天）
                'brandMouth' => App\GraphQL\Query\BrandMouthQuery::class,  //饼状销售额占比（截止到当月的）
                'sale' => App\GraphQL\Query\SaleQuery::class,    //折线图 音频类 销售额占比
                'zhengYL' => App\GraphQL\Query\ZhengYLQuery::class,  //郑伊露需要的数据
                'title' => App\GraphQL\Query\TitleQuery::class,   //标题
            ],
            'mutation' => [
                'createUser' => App\GraphQL\Mutation\CreateUserMutation::class,
                'updateUser' => App\GraphQL\Mutation\updateUserMutation::class,
                'deleteUser' => App\GraphQL\Mutation\deleteUserMutation::class,
                'userLogin' => App\GraphQL\Mutation\UserLoginMutation::class,
                'testObject' => App\GraphQL\Mutation\TestObjectMutation::class,
                'confirmOrder' => \App\GraphQL\Mutation\ConfirmOrderMutation::class,
                'changePwd' => \App\GraphQL\Mutation\ChangePwdMutation::class,

            ],
            'middleware' => []
        ],
    ],

    // The types available in the application. You can then access it from the
    // facade like this: GraphQL::type('user')
    //
    // Example:
    //
    // 'types' => [
    //     'user' => 'App\GraphQL\Type\UserType'
    // ]
    //
    'types' => [
        'user' => \App\GraphQL\Type\UserType::class,
        'employee' => \App\GraphQL\Type\EmployeeType::class,
        'billIndex' => \App\GraphQL\Type\BillIndexType::class,
//        'billList' => \App\GraphQL\Type\BillListType::class,
        'retailBill' => \App\GraphQL\Type\RetailBillType::class,
        'ptype' => \App\GraphQL\Type\PtypeType::class,
        'nVipCardSign' => \App\GraphQL\Type\NVipCardSignType::class,
        'goodsStock' => \App\GraphQL\Type\GoodsStockType::class,
        'pTypePrice' => \App\GraphQL\Type\PtypePriceType::class,
        'account' => \App\GraphQL\Type\AccountType::class,
        'test' => \App\GraphQL\Type\TestType::class,
        'goodsQty' => \App\GraphQL\Type\Input\GoodsQtyType::class,
        'discountMoney' => \App\GraphQL\Type\DiscontMoneyType::class,
        'return' => \App\GraphQL\Type\ReturnStringType::class,
        'autoPost' => \App\GraphQL\Type\AutoPostType::class,
        'vip' => \App\GraphQL\Type\VipType::class,
        'dayOrder' => \App\GraphQL\Type\DayOrderType::class,
        'totalOrder' => \App\GraphQL\Type\TotalOrderType::class,
        'brand' => \App\GraphQL\Type\BrandType::class,  //前一天的各品牌销售额占比
        'sale' => \App\GraphQL\Type\SaleType::class,    //15天的音频类销售占比
        'zhenYL' => \App\GraphQL\Type\ZhenYLType::class,    //郑伊露需要的数据
        'title' => \App\GraphQL\Type\TitleType::class,    //标题
    ],

    // This callable will be passed the Error object for each errors GraphQL catch.
    // The method should return an array representing the error.
    // Typically:
    // [
    //     'message' => '',
    //     'locations' => []
    // ]
    'error_formatter' => ['\Rebing\GraphQL\GraphQL', 'formatError'],

    // You can set the key, which will be used to retrieve the dynamic variables
    'params_key' => 'variables',

];
