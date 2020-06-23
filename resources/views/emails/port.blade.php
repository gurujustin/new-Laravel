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
            Hello {{$user->name}} , Update Happened For Gateway {{$gateway->name}}, 
        </h1>

        <h4>Ports Information</h4>
        <ul>
            @foreach ($data as $x => $vv)
                <li><h3>Port : {{$vv->Port}}</h3> ___  <h3> {{isset($oldData[$x]) ? $oldData[$x]->Port : ''}}</h3></li>
                <li>Status : {{$vv->Status}} ___ {{isset($oldData[$x]) ? $oldData[$x]->Status : ''}}</li>
                <li>IMSI : {{$vv->IMSI}} ___ {{isset($oldData[$x]) ? $oldData[$x]->IMSI : ''}}</li>
                <li>IMEI : {{$vv->IMEI}} ___ {{isset($oldData[$x]) ? $oldData[$x]->IMEI : ''}}</li>
                <li>Operator : {{$vv->Operator}} ___ {{isset($oldData[$x]) ? $oldData[$x]->Operator : ''}}</li>
                <li>CallStatus : {{$vv->CallStatus}} ___ {{isset($oldData[$x]) ? $oldData[$x]->CallStatus : ''}}</li>
                <li>Type : {{$vv->Type}} ___ {{isset($oldData[$x]) ? $oldData[$x]->Type : ''}}</li>
                <li>NetworkMode : {{$vv->NetworkMode}} ___ {{isset($oldData[$x]) ? $oldData[$x]->NetworkMode : ''}}</li>
                <li>Signal : {{$vv->Signal}} ___ {{isset($oldData[$x]) ? $oldData[$x]->Signal : ''}}</li>
                <li>ASR : {{$vv->ASR}} ___ {{isset($oldData[$x]) ? $oldData[$x]->ASR : ''}}</li>
                <li>ACD : {{$vv->ACD}} ___ {{isset($oldData[$x]) ? $oldData[$x]->ACD : ''}}</li>
                <li>PDD : {{$vv->PDD}} ___ {{isset($oldData[$x]) ? $oldData[$x]->PDD : ''}}</li>
            @endforeach
        </ul>
    </div>
</body>
</html>