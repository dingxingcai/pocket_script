<?php

namespace Tests\Feature;

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


        $s1 = Cache::add('8',['name'=>666,'sex'=>'男'],5);
        $nr = Cache::get('8');
        $n = $nr['name'];
        $s = 666;
        $m = [
            'name' => '666',
            'type' => 'hh'
        ];

        $this->post('/add/vip',[
            'name'=>'666',
            'id' =>10,
        ]);
        $key = md5($s);

        return response($m);

    }

    public function testLogin(){

        $time1 = microtime(true);

        $time2 = microtime(false);


        $this->call('post','/user/login',[
            'name'=>'admin',
            'password'=>"123456",
        ]);
    }

    public function testChangePwd(){
        $this->call('post','/user/changePwd',[
            'name' => 'admin',
            'token' => '79737dd49618a13a3d9f1722db4ab2c6',
            'oldPwd' => '123456',
            'newPwd1' => '666666',
            'newPwd2' => '666666'
        ]);
    }
}
