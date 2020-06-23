@extends('layouts.admin')

@section('content')
    <?php $currentRoute = Route::currentRouteName(); ?>
    <div class="container">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped table-dark">
                <thead>
                    <tr>
                        <th>Call Anaysis</th>
                        <th>Numbers</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analysis as $key => $value)
                    <tr>
                        <td>{{ $key }}</td>
                        <td>{{ $value }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div><br>
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped table-dark">
                <thead>
                    <tr>
                        <th>Port</th>
                        <th>Date Enabled</th>
                        <th>Date Disabled</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($port as $value)
                    <tr>
                        <td>{{ $value -> port }}</td>
                        <td>{{ $value -> DateEnabled }}</td>
                        <td>{{ $value -> DateDisabled }}</td>
                    </tr>
                    
                    @endforeach
                </tbody>
            </table>
        </div><br>
         
    </div>

@endsection
