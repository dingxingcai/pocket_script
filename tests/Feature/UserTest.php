<?php

namespace Tests\Feature;

use App\Jobs\Myjob;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Cache;
use Hash;

class UserTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUser()
    {


//        $s1 = Cache::add('8', ['name' => 666, 'sex' => '男'], 5);
//        $nr = Cache::get('8');
//        $n = $nr['name'];
        $s = 666;
        $url = "www.baidu.com/user?test=666&a=gddsf";
        $url = urlencode($url);
        $m = [
            'name' => '666',
            'type' => 'hh'
        ];

        $this->post('/add/vip', [
            'name' => '666',
            'id' => 10,
        ]);
        $key = md5($s);

        return response($m);

    }

    public function testLogin()
    {


        $this->call('post', '/user/test', [
            'name' => '99',
            'password' => "123456",
        ]);
    }

    public function testMail(){
        $this->call('post','/user/mail');
    }

    public function testChangePwd()
    {
        $this->call('post', '/user/changePwd', [
            'oldPwd' => '123456',
            'newPwd1' => '666666',
            'newPwd2' => '666666',
            'token' => 'a102dfbe746a40a3e45d6e1399f6317b'
        ]);
    }

    public function testList()
    {
        $res = $this->call('post', '/graphql', [
            'query' => 'mutation($goods:[retailBill], $vip:user){testObject(goods:$goods, vip:$vip){name,token,usercode}}',
            'variables'=>'{"goods":[{"PtypeId":1, "Qty":10},{"PtypeId":2, "Qty":2}], "vip":{"id":1001}}'
        ]);
    }

    public function testGraph()
    {
        $res = $this->call('post', '/graphql', [
            'query' => 'mutation($vip:user){testObject(vip:$vip){name,token,usercode}}',
            'variables'=>'{"vip":{"id":1}}'
        ]);
    }

    public function testGraphNormal()
    {
        $res = $this->call('post', '/graphql', [
            'query' => 'mutation($nId:Int,$vipNo:String,$goods:[goodsQty]){confirmOrder(vipNo:$vipNo,nId:$nId,goods:$goods){msg}}',
            'variables'=>'{"vipNo":"10002","nId":12,"goods":[{"typeId":"0000100398","Qty":2},{"typeId":"0000100399","Qty":4}]}'
        ]);
    }
}
