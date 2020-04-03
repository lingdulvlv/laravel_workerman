<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>登录</title>
	<script type="text/javascript" src="http://dev.csoeshow.com/home/js/jquery-1.11.3.min.js"></script>
</head>
<style>
</style>
<body>		
	<form action="{{url('/dologin')}}">
		<div style="width:200px; height:auto; margin:10% auto;">
			<div>账号：<input type="text" value="唉吆喂" name="name" /></div>
			<div>密码：<input type="text" value="123456" name="passwd" /></div>
			<input type="submit" value="登录" style="margin:10px auto; display: block;" />
		</div>
	</form>
</body>
</html>
<script type="text/javascript">

</script>