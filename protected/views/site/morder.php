<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>模拟用户订单完成</title>
</head>
<body>
	<form action="http://localhost/turingcat/api/orderfinish" method="post">
		<table align="center">
			<tr><td>第三方id</td><td><input type="text" name="third_id" size="100"></input></td></tr>
			<tr><td>openid</td><td><input type="text" name="openid" size="100"></input></td></tr>
			<tr><td>订单id</td><td><input type="text" name="order_id" size="100"></input></td></tr>
			<tr><td>token</td><td><input type="text" name="access_token" size="100"></input></td></tr>
			<tr><td></td><td><input type="submit" value="开始测试" size="100"></input></td></tr>
		</table>
	</form>
</body>
</html>