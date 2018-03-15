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
            'name' => '50',
            'password' => "666666",
        ]);
    }

    public function testChangePwd()
    {
        $this->call('post', '/user/changePwd', [
            'oldPwd' => '123456',
            'newPwd1' => '666666',
            'newPwd2' => '666666',
            'token' => '84f7592882bbef6a9388c094ab07beeb'
        ]);
    }


}
