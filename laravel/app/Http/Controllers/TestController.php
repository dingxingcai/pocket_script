<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Usr;
use Illuminate\Http\Request;
use DB;
use Redis;
use Cache;

class TestController extends Controller
{
    public function test(Request $request){

        $name = $request->input('name');
        $id = $request->get('id');
        $s = Employee::select('Fullname','UserCode','Department')->where('typeid','000010025000004')->get();

        return  response();
    }



    public function user(Request $request ,$id){
        echo $id;
    }
}
