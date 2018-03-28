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


        $s1 = Cache::add('8', ['name' => 666, 'sex' => '男'], 5);
        $nr = Cache::get('8');
        $n = $nr['name'];
        $s = 666;
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


        $this->call('post', '/user/login', [
            'name' => '99',
            'password' => "123456",
        ]);
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
            'query' => 'mutation($vip:String){testObject(vip:$vip){name,token,usercode}}',
            'variables'=>'{"vip":"18611367408"}'
        ]);
    }
}
