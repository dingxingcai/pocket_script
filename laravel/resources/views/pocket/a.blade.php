<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>哈哈哈</title>
</head>
<body>
<form action = "/add/vip" method="post">
    {{csrf_field()}}
    <div><input type="input" name ='name',value ="姓名" /></div>
    <div><input type="input" name ='address',value ="地址" /></div>
    <div>
        <input type="submit" value="提交" />
    </div>

</form>
</body>
</html>