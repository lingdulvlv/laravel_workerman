<html><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{$company->company_name}}PHP聊天室</title>
    <link href="{{asset('chat/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('chat/css/jquery-sinaEmotion-2.1.0.min.css')}}" rel="stylesheet">
    <link href="{{asset('chat/css/style.css')}}" rel="stylesheet">
  
    <script type="text/javascript" src="{{asset('chat/js/swfobject.js')}}"></script>
    <script type="text/javascript" src="{{asset('chat/js/web_socket.js')}}"></script>
    <script type="text/javascript" src="{{asset('chat/js/jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('chat/js/jquery-sinaEmotion-2.1.0.min.js')}}"></script>

    <script type="text/javascript">
    if (typeof console == "undefined") {    this.console = { log: function (msg) {  } };}
    // 如果浏览器不支持websocket，会使用这个flash自动模拟websocket协议，此过程对开发者透明
    WEB_SOCKET_SWF_LOCATION = "{{asset('chat/swf/WebSocketMain.swf')}}";
    // 开启flash的websocket debug
    WEB_SOCKET_DEBUG = true;
    var ws, name, client_list={};


    // 连接服务端
    function connect() {
       // 创建websocket
        ws = new WebSocket("ws://"+document.domain+":2346");
       // 当socket连接打开时，输入用户名
        ws.onopen = onopen;
       // 当有消息时根据消息类型显示不同信息
        ws.onmessage = onmessage; 
        ws.onclose = function() {
            console.log("连接关闭，定时重连");
            connect();
        };
        ws.onerror = function() {
            console.log("出现错误");
        };
    }

    // 连接建立时发送登录信息
    function onopen()
    {
        name = "{{$user->name}}"
        if(!name || name=='null'){  
            name = '游客';
        }
        // 登录
        var login_data = '{"type":"login","client_name":"'+name.replace(/"/g, '\\"')+'","room_id":"'+'{{$company->company_id}}'+'","uid":"'+'{{$user->id}}'+'"}';
        console.log("websocket握手成功，发送登录数据:"+login_data);
        ws.send(login_data);
    }

    // 服务端发来消息时
    function onmessage(e)
    {
        var data = JSON.parse(e.data);
        console.log(data)
        switch(data['type']){
            // 服务端ping客户端
            case 'ping':
                ws.send('{"type":"pong"}');
                break;;
            // 登录 更新用户列表
            case 'login':
                //{"type":"login","client_id":xxx,"client_name":"xxx","client_list":"[...]","time":"xxx"}
                $('.chat_content_0').append('<div class="join">'+data['client_name']+'进入聊天室</div>');
                // say(data['from_uid'], data['client_id'], data['client_name'],  data['client_name']+' 加入了聊天室', data['time']);
                break;
            // 发言
            case 'say':
                if(!data['to_uid']){
                    data['to_uid'] = 0;
                }
                //{"type":"say","from_client_id":xxx,"to_client_id":"all/client_id","content":"xxx","time":"xxx"}
                say(data['from_uid'], data['from_client_id'], data['from_client_name'], data['content'], data['time'], data['to_uid']);
                break;
            // 用户退出 更新用户列表
            case 'logout':
                //{"type":"logout","client_id":xxx,"time":"xxx"}
                // say(data['from_client_id'], data['from_client_name'], data['from_client_name']+' 退出了', data['time']);
                // delete client_list[data['from_client_id']];
                // flush_client_list();
        }
    }
    // 提交对话
    function onSubmit() {
        var input = document.getElementById("textarea");
        var to_uid = $('.chat_left .current').attr('data-block');
        ws.send('{"type":"say","content":"'+input.value.replace(/"/g, '\\"').replace(/\n/g,'\\n').replace(/\r/g, '\\r')+'","to_uid":"'+to_uid+'"}');
        input.value = "";
        input.focus();
    }

    // 发言
    function say(from_uid, from_client_id, from_client_name, content, time, to_uid){
        console.log('这是发言------'+from_uid+'------'+from_client_id+'------'+from_client_name+'------'+content+'------'+time+'----'+to_uid)
        if(to_uid == 0 || to_uid==undefined){//群聊
            $('.chat_content_0').append('<li class="chat_msg"><div class="chat_name">'+from_client_name+':</div><div class="chat_msg_desc">'+content+'</div></li>').parseEmotion();
        }else{
            var nowuid = {{$user->id}};
            @if($user->company_id!==0)//客服
                if(from_uid != nowuid){//接收别人的消息
                    // 左侧用户组
                    var is_set = $(".chat_left li.chat_"+from_uid).length;
                    if(!is_set){
                        $('.chat_left').append('<li class="chat_'+from_uid+'" data-block="'+from_uid+'"><a href="javascript:"><img src="http://dev.cola.com/chat/img/private.png"><p>'+from_client_name+'</p></a></li>');
                    }            
                    // 添加聊天内容块
                    $('.chat_desc').append('<div class="chat_content chat_content_'+from_uid+'"></div>')
                    // 右侧聊天内容区
                    $('.chat_content_'+from_uid).append('<li class="chat_msg"><div class="chat_name">'+from_client_name+':</div><div class="chat_msg_desc">'+content+'</div></li>').parseEmotion();
                }else{//回复
                    console.log('回复用户')
                    $('.chat_content_'+to_uid).append('<li class="chat_msg"><div class="chat_name">'+from_client_name+':</div><div class="chat_msg_desc">'+content+'</div></li>').parseEmotion();
                }
            @else
            if(from_uid != nowuid){//接收别人的消息
                $('.chat_content_'+from_uid).append('<li class="chat_msg"><div class="chat_name">'+from_client_name+':</div><div class="chat_msg_desc">'+content+'</div></li>').parseEmotion();
            }else{//主动发送
                $('.chat_content_'+to_uid).append('<li class="chat_msg"><div class="chat_name">'+from_client_name+':</div><div class="chat_msg_desc">'+content+'</div></li>').parseEmotion();
            }
            @endif
        }        
    }

    $(function(){
        select_client_id = 'all';
        $("#client_list").change(function(){
             select_client_id = $("#client_list option:selected").attr("value");
        });
        $('.face').click(function(event){
            $(this).sinaEmotion();
            event.stopPropagation();
        });
    });
    </script>

    <style type="text/css">
        body{ background:#eee; }
        ul,li{ margin:0; padding:0; list-style: none; }
        .chat{ width:300px; height:555px; background:#fff; margin:30px auto; }
        .chat_left{ width:62px; height:100%; float:left;}
        .chat_left li{ width:62px; height:; padding:10px 0; text-align:center; }
        .chat_left li a{ display: block; text-decoration: none;}
        .chat_left li img{ width:24px; height:24px; margin:0 auto; }
        .chat_left li p{ width:100%; text-align:center; margin:3px auto; color: #666; white-space: nowrap; text-overflow: ellipsis; overflow:hidden;}
        .chat_left li.current{ background: #F5FAFF; }


        .chat_right{ width:238px; height:100%; border-left:1px solid #EFEFEF; float:right;}
        .notice{ width:228px; height:72px; margin:5px; }
        .chat_desc{ width:228px; height:363px; margin:5px; }
        .chat_desc .join{ width:auto; max-width:150px; text-align:center; height:25px; line-height:25px; margin:2px auto; background:#F5FAFF; color:#ccc; font-size:14px; border-radius:3px;}
        .chat_text{ width:228px; height:90px; margin:5px; }
        .chat_right .chat_content{ width:100%; height:100%; display: none; overflow-y:auto;}
        .chat_right .chat_content:first-child{ display: block; }
        .chat_right .chat_content .chat_msg{ width:100%; height:auto; line-height:24px; font-size:14px; overflow:hidden;}
        .chat_right .chat_content .chat_msg .chat_name{ width:60px; height:auto; float:left; color:#7fa1cc;}
        .chat_right .chat_content .chat_msg .chat_msg_desc{ width:150px; height:auto; float:right; color:#555;}
        .textarea{ width:228px; height:60px; border:1px solid #E9E9E9; border-radius:4px; padding:5px; background:#fff;}
        .say_btn_line{ margin-top:5px; }
        .say_btn_line input{ color: #333; background-color: #ebebeb; border:1px solid #adadad; width:50px; height:25px; line-height:25px; text-align:center; float:left;}
        .say_btn_line input:last-child{ float:right; }
    </style>
</head>
<body onload="connect();">
    <div class="chat">
        <ul class="chat_left">
            <li class="chat_0 current" data-block=0>
                <a href="javascript:">
                    <img src="{{asset('chat/img/groupchat.png')}}">
                    <p>群聊</p>
                </a>
            </li>
            @if($user->company_id == 0)
                @foreach($consult_all as $key => $val)
                <li class="chat_{{$val->id}}" data-block="{{$val->id}}">
                    <a href="javascript:">
                        <img src="{{asset('chat/img/private.png')}}">
                        <p>{{$val->name}}</p>
                    </a>
                </li>
                @endforeach
            @else
                @foreach($uname_arr as $key => $val)
                <li class="chat_{{$key}}" data-block="{{$key}}">
                    <a href="javascript:">
                        <img src="{{asset('chat/img/private.png')}}">
                        <p>{{$val}}</p>
                    </a>
                </li>
                @endforeach            
            @endif
        </ul>
        <div class="chat_right">
            <div class="notice">
                公告内容！！！！
            </div>
            <div class="chat_desc">
                <div class="chat_content chat_content_0">
                    @if(isset($history[0]) && !empty($history[0]))
                        @foreach($history[0] as $key => $val)
                            <li class="chat_msg"><div class="chat_name">{{$val['name']}}:</div><div class="chat_msg_desc">{{$val['content']}}</div></li>
                        @endforeach
                    @endif
                </div>
                @if($user->company_id == 0)
                    @foreach($consult_all as $key => $val)
                    <div class="chat_content chat_content_{{$val->id}}">
                        @if(isset($history[$val->id]) && !empty($history[$val->id]))
                        @foreach($history[$val->id] as $k => $v)
                            <li class="chat_msg"><div class="chat_name">{{$v['name']}}:</div><div class="chat_msg_desc">{{$v['content']}}</div></li>
                        @endforeach
                        @endif
                    </div>
                    @endforeach
                @else
                    @foreach($uname_arr as $key => $val)
                    <div class="chat_content chat_content_{{$key}}">
                        @if(isset($history[$key]) && !empty($history[$key]))
                        @foreach($history[$key] as $k => $v)
                            <li class="chat_msg"><div class="chat_name">{{$v['name']}}:</div><div class="chat_msg_desc">{{$v['content']}}</div></li>
                        @endforeach
                        @endif
                    </div>
                    @endforeach 
                @endif
            </div>
            <div class="chat_text">
                <form onsubmit="onSubmit(); return false;">
                    <textarea class="textarea" id="textarea"></textarea>
                    <div class="say_btn_line">
                        <input type="button" class="face" value="表情" />
                        <input type="submit" class="" value="发表" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $('.chat_left').on('click', 'li', function(e) {
            console.log('点了生成的块')
            var data_block = $(this).attr('data-block');
            console.log(data_block)
            $('.chat_content').hide();
            $('.chat_content_'+data_block).show();
            $(this).addClass('current').siblings().removeClass('current');
        });
    </script>
    <script type="text/javascript">
        // 动态自适应屏幕
        document.write('<meta name="viewport" content="width=device-width,initial-scale=1">');
        $("textarea").on("keydown", function(e) {
            // 按enter键自动提交
            if(e.keyCode === 13 && !e.ctrlKey) {
                e.preventDefault();
                $('form').submit();
                return false;
            }

            // 按ctrl+enter组合键换行
            if(e.keyCode === 13 && e.ctrlKey) {
                $(this).val(function(i,val){
                    return val + "\n";
                });
            }
        });
    </script>
</body>
</html>
