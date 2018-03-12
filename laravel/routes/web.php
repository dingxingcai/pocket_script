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

Route::any('/add/vip',"TestController@test");

Route::get('user/{id}','TestController@user');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
