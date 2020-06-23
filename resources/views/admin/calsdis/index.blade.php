@extends('layouts.admin')

@section('content')
    <?php $currentRoute = Route::currentRouteName(); ?>
    <div class="container">
        <calsdis-dashboard
            :gw="{{isset($gw) ? json_encode($gw) : json_encode([])}}"
            :search="{{json_encode([
                "searchUrl" => route('calsdis') ,
                "searchPhrase" => $search
            ])}}"
            :urls="{{json_encode([
                'addUrl' => route('calsdis.store'),
                'exportUrl' => route('calsdis.export'),
                'importUrl' => route('calsdis.import'),
                'reportUrl' => route('cals.generate.report'),
                'getPortsUrl' => route('cals.get'),
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
