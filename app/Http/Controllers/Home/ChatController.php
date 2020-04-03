<?php

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
	/**
	 * 房间列表
	 * @return [type] [description]
	 */
    public function company()
    {
    	$res = \DB::table('company')->get();
    	return view('home.company',compact('res'));
    }

    /**
     * 聊天对话详情
     * @param  Request $request    [description]
     * @param  [type]  $company_id [description]
     * @return [type]              [description]
     */
    public function chat(Request $request, $company_id)
    {
    	if($request->session()->has('loginuser')){
            $user = $request->session()->get("loginuser");
            $company = \DB::table('company')->where('company_id',$company_id)->first();
    		$company_all = \DB::table('company')->get();
            $user_all = \DB::table('user')->get();
            return view('home.chat',compact('user','company','company_all'));
        }else{
        	return redirect('/login');
        }
    }


}
