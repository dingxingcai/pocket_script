<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return view('pocket.a');
});


//用户登录
Route::post('/user/login', "UserController@login");


//测试
Route::any('/user/test', "UserController@getInfo");

//测试发送邮件
Route::any('/user/mail', "TestController@sendMail");


Route::group(['middleware' => ['user']], function () {

    //修改密码
    Route::post('user/changePwd', "UserController@changePwd");

});


