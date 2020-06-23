@extends('layouts.admin')

@section('content')
    <?php $currentRoute = Route::currentRouteName(); ?>
    <div class="container">
        <ports-reports-dashboard
            :gw="{{isset($gw) ? json_encode($gw) : json_encode([])}}"
            :search="{{json_encode([
                "searchUrl" => route('portsReports') ,
                "searchPhrase" => $search
            ])}}"
            :urls="{{json_encode([
                'addUrl' => route('portReports.store'),
                'exportUrl' => route('portsReports.export'),
                'importUrl' => route('portsReports.import'),
                'reportUrl' => route('ports.generate.report'),
                'getPortsUrl' => route('ports.get'),
            ])}}"
            :pagination="{{json_encode([
                'total' => $total ,
                'perpage' => $perpage ,
                'offset'  => $offset,
                'url'    => route($currentRoute)
            ])}}"

        />
    </div>

@endsection
