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
            Hello {{$user->name}} , Update Happened For Gateway {{$gateway->name}}
        </h1>

        <h4>System Information </h4>
        <ul>
            <li>UP Time Formated : {{isset($data['SystemUpDuration']) ? gmdate('H:i:s',$data['SystemUpDuration']) : ''}}</li>
            @foreach ($data as $key => $value)
                <li>{{$key}} : {{$value}}</li>
            @endforeach
        </ul>
    
    </div>
</body>
</html>