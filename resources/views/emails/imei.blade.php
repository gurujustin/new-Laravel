<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>System Information</title>
</head>
<body>
    <div>
        <h1 style="text-align : center;">
            Hello {{$user->name}} , <br> Port {{$data->port}} System Updated Its IMEI From 
            {{$data->oldImei}} To {{$data->newImei}} <br>
            For Gateway {{$data->gateway->name}} <br>
            And Try Is Number {{$data->tryNumber + 1}}
        </h1>
    </div>
</body>
</html>