<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Helpers\ReportsAutomaticGenerator;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    public function run(){
        while(true){
            $waitTime = 60 * 5;        
            ReportsAutomaticGenerator::generate('port');
            sleep($waitTime);
        }
    }
}
