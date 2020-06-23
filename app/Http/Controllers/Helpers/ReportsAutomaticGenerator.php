<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\PortReportGenerate;
use Illuminate\Http\Request;
use App\CDR;
use App\Ports;
use App\Reports;
class ReportsAutomaticGenerator extends Controller
{
    public static function generate($type){
        set_time_limit(0);
        $dir = public_path().'/Reports';
        if(!is_dir($dir)){
            mkdir($dir);
        }
        $filename = 'LAST_PORT_REPORT_TIME.txt';

        $lastCheckDate = false;
        // if(file_exists($dir.'/'.$filename)){
        //     $lastCheckDate = (int)file_get_contents($dir.'/'.$filename);
        // }

        $lastReport = Reports::orderBy('id' , 'DESC')->where('reportType' , '=' , 'PORT_HOURLY')->limit(1)->get();
        if(!$lastReport->isEmpty()){
            $lastCheckDate = strtotime(explode(' ', explode('__', $lastReport[0]->dates)[0])[0]);
        }

        $ports = Ports::all()->map(function($item){
            return json_encode([
                'value' => $item->port,
                'text' => $item->port,
            ]);
        });

        $request = new Request();
        $hour = 60*60;
        $startDate = CDR::orderBy('calldate','ASC')->limit(1)->get()[0]->calldate;
        $parts = explode(' ', $startDate);
        $date = $parts[0];
        $hours = explode(':', $parts[1])[0];
        $startDate = strtotime($date.' '.$hours.':00:00');

        if($lastCheckDate){
            $startDate = $lastCheckDate;
        }

        $endDate = strtotime(CDR::orderBy('calldate','DESC')->limit(1)->get()[0]->calldate);

        $dates = [];
        $lastDate = date('Y-m-d', $startDate);

        while($startDate <= $endDate){
            $dates[] = [
                'date' => date('Y-m-d H:i:s', $startDate),
                'endDate' => date('Y-m-d H:i:s', $startDate + $hour),
                'type' => 'PORT_HOURLY'
            ];

            if(date('Y-m-d', $startDate) != $lastDate){
                $lastDate = date('Y-m-d', $startDate);
                $dates[] = [
                    'date' => date('Y-m-d H:i:s', $startDate - ($hour * 24)),
                    'endDate' => date('Y-m-d H:i:s', $startDate),
                    'type' => 'PORT_DAILY'
                ];
            }

            $startDate = $startDate + $hour;
        }

        foreach($dates as $date){
            $data = [
                'date' => $date['date'],
                'endDate' => $date['endDate'],
                'ports' => $ports,
                'store' => 'true',
                'title' => 'Port Report For Dates '.explode(' ',$date['date'])[1].' - '.explode(' ',$date['endDate'])[1],
                'reportType' => $date['type']
            ];
    
            $request->merge($data);
            $report = new PortReportGenerate($request);

            file_put_contents($dir.'/'.$filename, strtotime($date['endDate']));
        }

        return response()->json([
            'content' => 'DONE',
        ]);
    }
}
