@extends('layouts.admin')

@section('content')
    <?php $currentRoute = Route::currentRouteName(); ?>
    <div class="container">
        <reports-dashboard
            :gw="{{isset($gw) ? json_encode($gw) : json_encode([])}}"
            :search="{{json_encode([
                "searchUrl" => route('reports') ,
                "searchPhrase" => $search
            ])}}"
            :urls="{{json_encode([
                'addUrl' => route('report.store'),
                'exportUrl' => route('reports.export'),
                'importUrl' => route('reports.import'),
                'reportUrl' => route('reports.generate.report'),
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
