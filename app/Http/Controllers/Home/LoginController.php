<?php

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        return view('home.login');
    }


    public function dologin(Request $request)
    {
        $input = $request->all();
        $res = \DB::table('user')->where(['name'=>$input['name'], 'passwd'=>$input['passwd']])->first();
        if($res){
            $request->session()->put('loginuser', $res);
            $request->session()->save();
            return redirect('/company');
        }else{
            return redirect('/login');
        }
    }











}
