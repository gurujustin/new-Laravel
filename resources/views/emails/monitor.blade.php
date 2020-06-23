<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Monitor Stopped</title>
</head>
<body>
    <div class="message_container" style="padding : 10px;margin : 0px 30px auto; border : 1px solid #DDD; 
    border-radius : 10px;">
        <h3 style="text-align: center">
            Hello {{$user->name}}, <br> Monitor {{$monitor->name}} Has Stopped Working At 
            {{date('Y-m-d h:i:s')}} And it was working since
            {{$monitor->updated_at}}
            <br> And System Says That This is The Error Reason
            <br>
            {{json_encode($error)}}
        </h3>
        <h4>Monitor Details</h4>
        <ul>
            @foreach($monitor->toArray() as $key => $value)
                <li>{{$key}} : {{$value}}</li>
            @endforeach
        </ul>
        <hr>
        <h4>User Details</h4>
        <ul>
            @foreach($user->toArray() as $key => $value)
                <li>{{$key}} : {{$value}}</li>
            @endforeach
        </ul>
    </div>
</body>
</html>