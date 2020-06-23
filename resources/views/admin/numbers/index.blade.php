@extends('layouts.admin')

@section('content')
    <?php $currentRoute = Route::currentRouteName(); ?>
    <div class="container">
        <numbers-dashboard
            :gw="{{isset($gw) ? json_encode($gw) : json_encode([])}}"
            :search="{{json_encode([
                "searchUrl" => route('numbers') ,
                "searchPhrase" => $search
            ])}}"
            :urls="{{json_encode([
                "searchUrl" => route('numbers') ,
                'addUrl' => route('number.store'),
                'exportUrl' => route('numbers.export'),
                'importUrl' => route('numbers.import'),
            ])}}"
        />
    </div>

@endsection
