<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Gateway Stopped</title>
</head>
<body>
    <div class="message_container" style="padding : 10px;margin : 0px 30px auto; border : 1px solid #DDD; 
    border-radius : 10px;">
        <h3 style="text-align: center">
            Hello {{$username}}, <br> Gateway {{$gateway_name}} Has Went 
            {{$message_data}} At 
            {{date('Y-m-d h:i:s')}} 
        </h3>
    </div>
</body>
</html>