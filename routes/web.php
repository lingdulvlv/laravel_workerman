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

Route::group(['namespace' => 'Home'], function () {
	Route::get("login","LoginController@login");//登录
	Route::get("dologin","LoginController@dologin");//登录处理
	Route::get("company","ChatController@company");//公司列表
	Route::get("chat/{id}","ChatController@chat");//公司聊天室
});
