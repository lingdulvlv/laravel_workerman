<?php

namespace App\Events;

use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class Workerman
{
    /**
    * 有消息时
    * @param int $client_id
    * @param mixed $message
    */
    public static function onMessage($client_id, $message)
    {
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n";
        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }
        // 根据类型执行不同的业务
        switch($message_data['type'])
        {
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                // 判断是否有房间号
                if(!isset($message_data['room_id']))
                {
                    throw new \Exception("\$message_data['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                
                // 把房间号昵称放到session中
                $room_id = $message_data['room_id'];
                $uid = $message_data['uid'];
                $client_name = htmlspecialchars($message_data['client_name']);
                $_SESSION['room_id'] = $room_id;
                $_SESSION['client_name'] = $client_name;
                $_SESSION['uid'] = $uid;
                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx} 
                $new_message = array('type'=>$message_data['type'], 'from_uid'=>$uid, 'client_id'=>$client_id, 'client_name'=>htmlspecialchars($client_name), 'time'=>date('Y-m-d H:i:s'));
                // 查看该用户是否已经在房间了
                $is_set = Gateway::isUidOnline($uid);
                if(!$is_set){//说明刚进入房间
                    // 查询是否进入过房间
                    if(\Cache::has('uid_company')){
                        $uid_company = \Cache::get('uid_company');
                    }else{
                        $uid_company = [];
                    }
                    if(!empty($uid_company)){
                        foreach($uid_company as $key => $val){
                            if($val['uid'] == $uid){//说明该用户之前进入过该房间
                                $is_set = 1;
                                break;
                            }
                        }
                    }
                    if(!$is_set){
                        $res_join = DB::table('record')->where(['uid'=>$uid, 'company_id'=>$room_id, 'read'=>2])->first();
                        if(empty($res_join)){
                            $uid_room = [
                                'uid' => $uid,
                                'company_id' => $room_id,
                                'create_time' => time(),
                                'read' => 2
                            ];
                            array_push($uid_company, $uid_room);
                            \Cache::put('uid_company', $uid_company, 20);
                            Gateway::sendToGroup($room_id, json_encode($new_message));   
                        }
                    }
                }
                Gateway::bindUid($client_id, $uid);
                Gateway::joinGroup($client_id, $room_id);
                // 给当前用户发送用户列表 
                // $new_message['client_list'] = $clients_list;
                Gateway::sendToCurrentClient(json_encode($new_message));

                
                return;
                
            // 客户端发言 message: {type:say, to_client_id:xx, content:xx}
            case 'say':
                // 非法请求
                if(!isset($_SESSION['room_id']))
                {
                    throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }
                $room_id = $_SESSION['room_id'];
                $client_name = $_SESSION['client_name'];
                $uid =  Gateway::getUidByClientId($client_id);
                $record = [
                    'uid' => $uid,
                    'content' => nl2br(htmlspecialchars($message_data['content'])),
                    'to_uid' => $message_data['to_uid'],
                    'company_id' => $room_id,
                    'create_time' => time(),
                    'read' => 1
                ];
                // 私聊
                if($message_data['to_uid'] != 0 || !empty($message_data['to_uid']))
                {
                    $new_message = array(
                        'type'=>'say',
                        'from_uid' => $uid,
                        'from_client_id'=>$client_id, 
                        'from_client_name' =>$client_name,
                        // 'to_client_id'=>$message_data['to_client_id'],
                        'content'=>nl2br(htmlspecialchars($message_data['content'])),
                        'time'=>date('Y-m-d H:i:s'),
                        'to_uid' => $message_data['to_uid']
                    );
                    $client_arr = Gateway::getClientIdByUid($message_data['to_uid']);
                    $is_on = Gateway::isUidOnline($message_data['to_uid']);
                    if(empty($client_arr) || empty($is_on)){//说明对方不在线
                        $record['read'] = 0;
                        echo "对方不在线\n";
                    }
                    foreach($client_arr as $key => $val){
                        Gateway::sendToClient($val, json_encode($new_message)); //向对方发送
                    }
                    // $new_message['content'] = "<b>你对".htmlspecialchars($message_data['to_client_name'])."说: </b>".nl2br(htmlspecialchars($message_data['content']));
                    $new_message['content'] = nl2br(htmlspecialchars($message_data['content']));

                    //聊天记录存储到缓存中
                    if(\Cache::has('record')){
                        $record_arr = \Cache::get('record');
                    }else{
                        $record_arr = [];
                    }
                    array_push($record_arr, $record);
                    \Cache::put('record', $record_arr, 20);
                    return Gateway::sendToCurrentClient(json_encode($new_message));//给当前客户端发送
                }
                if(\Cache::has('record')){
                    $record_arr = \Cache::get('record');
                }else{
                    $record_arr = [];
                }
                array_push($record_arr, $record);
                \Cache::put('record', $record_arr, 20);
                $new_message = array(
                    'from_uid' => $uid,
                    'type'=>'say', 
                    'from_client_id'=>$client_id,
                    'from_client_name' =>$client_name,
                    // 'to_client_id'=>'all',
                    'content'=>nl2br(htmlspecialchars($message_data['content'])),
                    'time'=>date('Y-m-d H:i:s'),
                    'to_uid' => 0
                );
                return Gateway::sendToGroup($room_id ,json_encode($new_message));
        }
    }
   
   /**
    * 当客户端断开连接时
    * @param integer $client_id 客户端id
    */
    public static function onClose($client_id)
    {
        // debug
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
       
        // 从房间的客户端列表中删除
        if(isset($_SESSION['room_id']))
        {
            $room_id = $_SESSION['room_id'];
            $new_message = array('type'=>'logout', 'from_client_id'=>$client_id, 'from_client_name'=>$_SESSION['client_name'], 'time'=>date('Y-m-d H:i:s'));
            Gateway::sendToGroup($room_id, json_encode($new_message));
        }
    }

    /**
     * 获取当前房间的用户client_id
     */
    public static function getClientByGroup($room_id='')
    {
        if(is_numeric($room_id)){
            return Gateway::getClientSessionsByGroup($room_id);
        }else{
            return Gateway::getAllClientSessions();
        }
        
    }
}
