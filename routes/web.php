<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Auth::routes();


/**
 * all admin routes
 */
Route::group(['middleware' => ['\App\Http\Middleware\Admin']] , function(){
    /**
     * the dashboard
     */
    Route::get('/home', 'HomeController@index')->name('home');

    /**
     * users routes
     */
    Route::get('/users' , 'UsersController@index')->name('users');
    // store
    Route::post('/user/store' , 'UsersController@store')->name('user.store');
    // show
    Route::get('/user/show/{id}' , 'UsersController@show')->name('user.show');
    // edit
    Route::get('/user/edit/{id}' , 'UsersController@edit')->name('user.edit');
    // update
    Route::post('/user/update/{id}' , 'UsersController@update')->name('user.update');
    // delete
    Route::post('/user/delete/{id}' , 'UsersController@destroy')->name('user.delete');
    // export
    Route::get('/users/export/all' , 'UsersController@exportAll')->name('users.export');
    // import
    Route::post('/users/import' , 'UsersController@importJson')->name('users.import');

    // generate report
    Route::get('/reports/generate' , 'ReportsController@generateReport')->name('reports.generate.report');
    Route::get('/ports/generate' , 'PortReportsController@generateReport')->name('ports.generate.report');
    Route::get('/ports/get' , 'PortReportsController@getPorts')->name('ports.get');
    // reports generator
    Route::get('/reports/generate/{type}' , 'Helpers\\ReportsAutomaticGenerator@generate')->name('reports.generate.type');
    Route::get('/monitor/run' , 'MonitorController@run')->name('monitor.run');
    //generate calls report
    Route::get('/cals/generate' , 'CalsDisController@generateReport')->name('cals.generate.report');
    Route::get('/cals/get' , 'CalsDisController@getPorts')->name('cals.get');

    $routesContainer = [
        [
            'names' => 'reports',
            'name' => 'report',
            'controller' => 'ReportsController',
        ],
        [
            'names' => 'portsReports',
            'name' => 'portReports',
            'controller' => 'PortReportsController',
        ],
        [
            'names' => 'numbers',
            'name' => 'number',
            'controller' => 'NumbersController',
        ],
        [
            'names' => 'analysis',
            'name' => 'analysis',
            'controller' => 'AnalysisController',
        ],
        [
            'names' => 'calsdis',
            'name' => 'calsdis',
            'controller' => 'CalsDisController',
        ],
    ];

    foreach($routesContainer as $r){
        /**
         * scripts routes
         */
        Route::get('/'.$r['names'] , $r['controller'].'@index')->name($r['names']);
        // store
        Route::post('/'.$r['name'].'/store' , $r['controller'].'@store')->name($r['name'].'.store');
        // show
        Route::get('/'.$r['name'].'/show/{id}' , $r['controller'].'@show')->name($r['name'].'.show');
        // edit
        Route::get('/'.$r['name'].'/edit/{id}' , $r['controller'].'@edit')->name($r['name'].'.edit');
        // update
        Route::post('/'.$r['name'].'/update/{id}' , $r['controller'].'@update')->name($r['name'].'.update');
        // delete
        Route::post('/'.$r['name'].'/delete/{id}' , $r['controller'].'@destroy')->name($r['name'].'.delete');
        // export
        Route::get('/'.$r['names'].'/export/all' , $r['controller'].'@exportAll')->name($r['names'].'.export');
        // import
        Route::post('/'.$r['names'].'/import' , $r['controller'].'@importJson')->name($r['names'].'.import');
    }

    // /**
    //  * the test url
    //  */
    // Route::get('/test/get' , 'TestController@get')->name('test');
});
