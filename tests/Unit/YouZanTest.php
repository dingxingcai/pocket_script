<?php

namespace Tests\Unit;

use App\Services\YouZanClient;
use App\Services\YouZanService;
use Tests\TestCase;
use Youzan\Open\Client;

class YouZanTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAccessToken()
    {
        $accessToken1 = YouZanService::accessToken();
        $accessToken2 = YouZanService::accessToken();

        print_r([1 => $accessToken1, 2 => $accessToken2]);
    }

    public function testTradeList()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.trades.sold.get';
        $apiVersion = '3.0.0';

        $params = [
//            'alias' => 'fa8989ad342k',
//            'buyer_id' => '737287904',
//            'type' => 'FIXED'
//            'type' => 'QRCODE'

            'buyer_id' => '742033847',
            'type' => 'QRCODE'
        ];

        $response = $client->get($method, $apiVersion, $params);
        var_dump($response);
    }

    public function testTradeListByStartUpdate()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.trades.sold.get';
        $apiVersion = '3.0.0';

        $params = [
            'end_created' => '2018-04-30 15:00',
            'start_created' => '2018-04-30 14:00',
            'page_size' => 10,
            'page_no' => 1
        ];


        $response = $client->get($method, $apiVersion, $params);
        var_dump($response);
    }

    public function testCardList()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.card.list';
        $apiVersion = '3.0.0';

        $params = [
//            'alias' => 'fa8989ad342k',
        ];

        $response = $client->get($method, $apiVersion, $params);
        var_dump($response['response']);
    }

    public function testCardUsers()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.customer.search';
        $apiVersion = '3.0.0';


        $cardList = YouZanService::getCardList();
        foreach ($cardList as $card) {
            $params = [
                'page' => 1,
                'card_alias' => $card['card_alias'],
            ];

            $files = [];
            $response = $client->post($method, $apiVersion, $params, $files);
            $result = $response['response'];
            print_r($result);
         }


//        var_dump($result);
//        $my_files = [
//        ];
//
//        $client = new \YZTokenClient($accessToken);
//        $result = $client->post($method, $apiVersion, $params, $my_files);
    }

    public function testCardDetail()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.card.get';
        $apiVersion = '3.0.0';

        $cardList = YouZanService::getCardList();
        foreach ($cardList['items'] as $card) {
            $params = [
                'card_alias' => $card['card_alias'],
            ];

            $files = [];
            $response = $client->get($method, $apiVersion, $params);
            $result = $response['response'];
            var_dump($result);
        }
    }

    public function testCustomerCardList()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.customer.card.list';
        $apiVersion = '3.0.0';

        $params = [
            'page' => 1,
            'mobile' => '15911094370',
        ];

        $response = $client->get($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testCustomerInfo()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.customer.info.get';
        $apiVersion = '3.0.0';

        $params = [
            'card_no' => '233967531307746141',
        ];

        $response = $client->post($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testCardGrant()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.customer.card.grant';
        $apiVersion = '3.0.0';

        $params = [
            'mobile' => '18519399410', //15911094367
            'card_alias' => '365dfnbl8ly1yD',
            'fans_type' => 1,
            'fans_id' => 0
        ];

        $response = $client->post($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testCardDelete()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.customer.card.delete';
        $apiVersion = '3.0.0';

        $params = [
            'mobile' => '18519399410', //15911094367
            'card_alias' => '365dfnbl8ly1yD',
            'card_no' => '',
            'fans_type' => 1,
            'fans_id' => 0
        ];

        $response = $client->post($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testCardEnable()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

//        for($index = 0 ; $index < 25; $index++){
//            $method = $index % 2 ? 'youzan.scrm.card.enable' : 'youzan.scrm.card.disable';
//
//            $apiVersion = '3.0.0';
//
//            $params = [
//                'card_alias' => '365dfnbl8ly1yD',
//            ];
//
//            $response = $client->post($method, $apiVersion, $params);
//            $result = $response['response'];
//            var_dump($result);
//        }
        $method = 'youzan.scrm.card.enable';

//        $method = 'youzan.scrm.card.disable';
        $apiVersion = '3.0.0';

        $params = [
            'card_alias' => '365dfnbl8ly1yD',
        ];

        $response = $client->post($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testTradeGet()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.trade.get';
        $apiVersion = '3.0.0';

        $params = [
            'tid' => 'E20180423170104057700006',
        ];

        $response = $client->get($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);

//        fans_id => 2022742744
//        buyer_id => 304316094
    }

    public function testOpenidGet()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.user.weixin.openid.get';
        $apiVersion = '3.0.0';

        $params = [
            'mobile' => '13709413994',
        ];

        $response = $client->get($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testCustomerCreate()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.customer.create';
        $apiVersion = '3.0.0';

        $params = [
            'mobile' => '18500300265',
            'customer_create' => json_encode(['name' => '马助']),
        ];

        $response = $client->post($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testCustomerGet()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.customer.get';
        $apiVersion = '3.1.0';

        $params = [
//            'account' => json_encode(["account_type"=>"Mobile", "account_id"=>"18611367408"]),
            'account' => '{"account_type":"Mobile", "account_id":"17098931296"}',
        ];

        $response = $client->get($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testCustomerGetByYouZanAccount()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.customer.get';
        $apiVersion = '3.1.0';

        $params = [
//            'account' => json_encode(["account_type"=>"Mobile", "account_id"=>"18611367408"]),
            'account' => '{"account_type":"YouZanAccount", "account_id":"725462007"}'
//            'account' => '{"account_type":"FansID", "account_id":"2022742744"}'
        ];
//        fans_id => 2022742744
//        buyer_id => 304316094

        $response = $client->get($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testCustomerGetByFansId()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.customer.get';
        $apiVersion = '3.1.0';

        $params = [
//            'account' => json_encode(["account_type"=>"Mobile", "account_id"=>"18611367408"]),
            'account' => '{"account_type":"FansID", "account_id":"4874214531"}'
//            'account' => '{"account_type":"FansID", "account_id":"2022742744"}'
        ];
//        fans_id => 2022742744
//        buyer_id => 304316094

        $response = $client->get($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testGetCustomerGouCiByMobile()
    {
        $res = YouZanService::getCustomerGouCiByMobile('13903008198');
    }

    public function testCustomerUpdate()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.scrm.customer.update';
        $apiVersion = '3.0.0';

        $params = [
//            'account' => json_encode(["account_type"=>"Mobile", "account_id"=>"18611367408"]),
            'account' => '{"account_type":"Mobile", "account_id":"18500300265"}',
            'customer_update' => json_encode(["contact_address"=>['address' => '广州']])
        ];

        $response = $client->get($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testPointsSync()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.crm.customer.points.sync';
        $apiVersion = '3.0.0';

        $params = [
            'mobile' => '18611367408',
            'points' => 6,
            'reason' => '同步积分'
        ];

        $response = $client->post($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testPointsGet()
    {
        $accessToken = YouZanService::accessToken();
        $client = new Client($accessToken);

        $method = 'youzan.crm.fans.points.get';
        $apiVersion = '3.0.0';

        $params = [
            'mobile' => '18611367408'
//            'account' => json_encode(["account_type"=>"Mobile", "account_id"=>"18611367408"]),
//            'account' => '{"account_type":"Mobile", "account_id":"18611367408"}',
//            'customer_update' => json_encode(["contact_address"=>['address' => '北京']])
        ];

        $response = $client->get($method, $apiVersion, $params);
        $result = $response['response'];
        var_dump($result);
    }

    public function testEnsureCustomerExisted()
    {
        YouZanService::ensureCustomerExisted('18611367408');
    }

    public function testItemsInventoryGet()
    {
        $response = (new YouZanClient(YouZanService::accessToken()))->get(
            'youzan.items.inventory.get', '3.0.0', [
            'order_by' => 'update_time',
            'page_no' => 0,
            'page_size' => 5
        ]);
    }

    public function testItemGet()
    {
//        $response = (new YouZanClient(YouZanService::accessToken()))->get(
//            'youzan.item.get', '3.0.0', [
//            'item_id' => '409423756',
//        ]);


        $response = (new YouZanClient(YouZanService::accessToken()))->get(
        'youzan.item.get', '3.0.0', [
            'item_id' => '410163896',
        ]);

        print_r($response);
    }

}
