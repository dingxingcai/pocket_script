<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUser()
    {
        $employee = $this->call('post','/add/vip',[
            'name'=>'666',
            'id' =>10,
            'token'=>csrf_token()
        ]);
        $s = 666;
        $m = [
            'name' => '666',
            'type' => 'hh'
        ];

        $this->post('/add/vip',[
            'name'=>'666',
            'id' =>10,
            'token'=>csrf_token()
        ]);
        $key = md5($s);

        return response($m);

    }
}
