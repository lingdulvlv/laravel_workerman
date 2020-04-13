<?php

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

use App\Events\Workerman;
class ChatController extends Controller
{
	/**
	 * 房间列表
	 * @return [type] [description]
	 */
    public function company(Request $request)
    {
        if($request->session()->has('loginuser')){
            $user = $request->session()->get("loginuser");
            if($user->company_id !=0){
                $res = DB::table('company')->where('company_id',$user->company_id)->get();
            }else{
                $res = \DB::table('company')->get();
            }
            return view('home.company',compact('res'));
        }else{
            return redirect('/login');
        }
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
            $consult_all = \DB::table('user')->where('company_id',$company_id)->get();
            //聊天记录
            $history = [];
            $uid = $user->id;
            if($user->company_id == 0){//用户，非客服
                $consult_id = [];
                if($consult_all->count()){
                    foreach($consult_all as $key => $val){
                        $consult_id[] = $val->id;
                    }
                }
                $res_chat = DB::table('record as r')->join('user as u','r.uid','=','u.id')->where('r.read','!=',2)->where(['r.company_id'=>$company_id, 'r.to_uid'=>0])->Orwhere(function($query) use($uid, $company_id){
                    $query->where('r.company_id',$company_id)->where('r.uid',$uid)->where('r.read','!=',2);
                })->Orwhere(function($query) use ($uid, $consult_id, $company_id){
                    $query->where('r.company_id',$company_id)->where('r.to_uid',$uid)->whereIn('r.uid',$consult_id)->where('r.read','!=',2);
                })->select('r.id','r.uid','r.to_uid','r.content','r.create_time','u.name')->orderBy('r.create_time','asc')->get();
            }else{//客服
                $res_chat = DB::table('record as r')->join('user as u','r.uid','=','u.id')->where('r.read','!=',2)->where(['r.company_id'=>$company_id, 'r.to_uid'=>0])->Orwhere(function($query) use($uid, $company_id){
                    $query->where('r.company_id',$company_id)->where('r.uid',$uid)->where('r.read','!=',2);
                })->Orwhere(function($query) use ($uid, $company_id){
                    $query->where('r.company_id',$company_id)->where('r.to_uid',$uid)->where('r.read','!=',2);
                })->select('r.id','r.uid','r.to_uid','r.content','r.create_time','u.name')->orderBy('r.create_time','asc')->get();
            }
            $last_time = 0;//数据库中最后的时间
            $new_time = 0;//缓存中最新的时间，如果为0，则都在数据库中记录了
            $uname_arr = [];//客服时左侧用户列表
            if($res_chat->count()){
                $last_time = $res_chat[intval($res_chat->count()-1)]->create_time;
                foreach($res_chat as $key => $val){
                    if($val->to_uid == 0){//公共
                        $history[0][] = [
                            'id' => $val->id,
                            'uid' => $val->uid,
                            'to_uid' => $val->to_uid,
                            'content' => $val->content,
                            'create_time' => date('Y-m-d H:i:s',$val->create_time),
                            'name' => $val->name,
                        ];
                    }else{
                        $k = ($val->uid == $uid) ? $val->to_uid : $val->uid;
                        $history[$k][] = [
                            'id' => $val->id,
                            'uid' => $val->uid,
                            'to_uid' => $val->to_uid,
                            'content' => $val->content,
                            'create_time' => date('Y-m-d H:i:s',$val->create_time),
                            'name' => $val->name,
                        ];
                        $uname_arr[$k] = $val->name;
                    }
                }
            }
            $record = \Cache::get('record');
            if(empty($history)){
                $history[0] = [];
            }
            if(!empty($record)){
                if(isset($record[intval(count($record)-1)]['create_time'])){
                    $new_time = $record[intval(count($record)-1)]['create_time'];
                }
                // echo $new_time,'----',$last_time,"<br>";
                if($new_time > $last_time){//说明缓存中有数据库未记录的数据
                    $push_arr = [];
                    $uid_arr = [];
                    foreach($record as $key => $val){
                        if($val['company_id']==$company_id && $val['create_time'] > $last_time && ($val['uid']==$uid || $val['to_uid']==$uid || $val['to_uid']==0)){
                            $push_arr[] = $val;
                            $uid_arr[] = $val['uid'];
                        }
                    }
                    $uid_arr = array_filter(array_unique($uid_arr));
                    //查询缓存中的用户姓名
                    $res_uid = DB::table('user')->whereIn('id',$uid_arr)->select('id','name')->get();
                    foreach($push_arr as $key => $val){
                        $name = '';
                        foreach($res_uid as $k => $v){
                            if($val['uid'] == $v->id){
                                $name = $v->name;
                                break;
                            }
                        }
                        $val['name'] = $name;
                        if($val['to_uid'] == 0){
                            array_push($history[0], $val);
                        }else{
                            $push_k = ($val['uid'] == $uid) ? $val['to_uid'] : $val['uid'];
                            $history[$push_k][] = $val;
                            $uname_arr[$push_k] = $name;
                        }
                    }
                }
            }
            return view('home.chat',compact('user','company','company_all','consult_all', 'history','uname_arr'));
        }else{
        	return redirect('/login');
        }
    }

    /**
     * 获取该房间的客服的client_id
     */
    public function getConsultClient(Request $request)
    {
        $company_id = $request->input('company_id');
        $workerman = new Workerman();
        $client = $workerman::getClientByGroup($company_id);
        return $this->ok(['code'=>200, 'data'=>$client]);
    }


}
