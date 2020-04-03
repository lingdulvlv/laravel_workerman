<html><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>房间列表</title>
    <style type="text/css">
    .center{ width:800px; height:100%; background:#F5FAFF; margin:0 auto;}
    .room{ width:200px; height:100px; border:1px solid red; text-align:center; line-height:100px; float:left; margin:5px; }
    a{ color:green; }
    </style>
</head>
<body>
    <div class="center">
        @foreach($res as $key => $val)
        <a href="/chat/{{$val->company_id}}" target="_blank">
            <div class="room">
                {{$val->company_name}}
            </div>
        </a>
        @endforeach
    </div>
</body>
</html>
