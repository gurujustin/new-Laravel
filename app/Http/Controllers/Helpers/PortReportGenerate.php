<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CDR;
use App\Routes;
use App\Ports;
use App\Reports;
use DB;

class PortReportGenerate extends Controller
{
    protected $alert = [];
    protected $date = '';
    protected $secondDate = '';
    protected $reportId = 0;

    protected $cdrRecords = [];
    protected $cdrRecordsCount = 0;
    protected $routesRecords = [];
    protected $routesRecordsCount = 0;
    protected $portsRecords = [];
    protected $portsRecordsCount = 0;

    protected $perpage = 0;
    protected $offset = 0;

    protected $summary = [];
    protected $detailed = [];

    protected $callStatuses = ['FAILED', 'NO ANSWER', 'BUSY', 'ANSWERED'];
    protected $dispositions = [];

    protected $dates = [];

    public function __construct($request){
        ini_set("memory_limit","1000M");

        $this->date = isset(explode(' ' , str_replace('+' , ' ',$request->date))[1]) ? str_replace('+' , ' ',$request->date) : str_replace('+' , ' ',$request->date). ' 00:00:00';
        $this->secondDate = !empty($request->endDate) ? str_replace('+' , ' ',$request->endDate) : date('Y-m-d H:i:s', strtotime($this->date) + 24*60*60);
        
        // generate dates
        $startDate = strtotime($this->date);
        $endDate = strtotime($this->secondDate);
        $lastDate = '';
        $hour = 60*60;

        while($startDate < $endDate){
            if(date('Y-m-d', $startDate) != $lastDate){
                $lastDate = date('Y-m-d',$startDate);
                $this->dates[] = date('Y-m-d', $startDate);
            }
            $startDate = $startDate + $hour;
        }

        $perpage = !empty($request->perpage) && $request->perpage > 0 ? $request->perpage : 1000000000000000;
        $offset = !empty($request->offset) ? $request->offset : 0;

        // $this->withDates = !empty($request->withDates) && $request->withDates != false ? true : false;

        // dd($this->withDates);

        $this->perpage = $perpage;
        $this->offset = $offset;

        $this->reportId = $request->reportId;

        $this->ports = !empty($request->ports) ? collect($request->ports)->map(function($item){
            return json_decode($item)->value;
        }) : Ports::all()->map(function($item){
            return $item->port;
        });

        $type = !empty($request->type) ? $request->type : 'SUMMARY';
        $action = !empty($request->action) ? $request->action : 'GENERATE';
        $target = !empty($request->target) ? $request->target : 'ALL';
        $section = !empty($request->section) ? $request->section : 'ALL';
        $store = !empty($request->store) ? $request->store : 'false';
        $reportType = !empty($request->reportType) ? $request->reportType : 'PORT_CUSTOM';

        // if report already saved or static
        if(!empty($this->reportId)){
            $report = Reports::find($this->reportId);
            $reportData = json_decode(file_get_contents(public_path()."/Reports/".$report->report), true);
            $data = $reportData['detailed'];
            $this->summary = $reportData['summary'];
        }else{
            foreach($this->ports as $port){
                // cdr data
                if($target == 'ALL' || $target == 'CDR'){
                    $this->cdrRecords["port-$port"] = CDR::
                    where([['calldate', '>=', $this->date], ['calldate', '<', $this->secondDate], ['port' , '=', (int)$port]])
                    ->whereNotNull('dst')->whereNotNull('clid')->where('dst', '!=' , '')->where('clid', '!=' , '')
                    ->get();
    
                    $this->dispositions["port-$port"] = collect($this->cdrRecords['port-'.$port])->unique('userfield');
                }
    
    
                // routes data
                if($target == 'ALL' || $target == 'ROUTES'){
                    // , ['port' , '=', (int)$port]
                    $this->routesRecords["port-$port"] = Routes::
                    where([['DateEnabled', '>=', $this->date], ['DateEnabled', '<', $this->secondDate], ['port' , '=', (int)$port]])
                    ->orWhere([['DateDisabled', '>=', $this->date], ['DateDisabled', '<', $this->secondDate], ['port' , '=', (int)$port]])
                    ->get();
                }
    
                // ports data
                if($target == 'ALL' || $target == 'PORTS'){
                    $this->portsRecords["port-$port"] = Ports::
                    where([['dateenabled', '>=', $this->date], ['dateenabled', '<', $this->secondDate], ['port' , '=', (int)$port]])
                    ->orWhere([['datedisabled', '>=', $this->date], ['datedisabled', '<', $this->secondDate], ['port' , '=', (int)$port]])
                    ->get();
                }
            }
    
            if(!empty($this->dispositions)){
                // dd($this->dispositions);
                $sub = [];
                foreach($this->ports as $port){
                    $sub[] = collect($this->dispositions["port-$port"])->unique('userfield');
                }
                $this->dispositions = [];
                foreach($sub as $value){
                    foreach($value as $val){ $this->dispositions[] = $val; }
                }
                
                $sub = collect($this->dispositions)->unique('userfield');
                $this->dispositions = [];
                foreach($sub as $disposition){
                    $this->dispositions[] = empty($disposition->userfield) ? 'NULL' : $disposition->userfield;
                }
            }
        }

        if($type == 'SUMMARY' || $store == 'true'){
            if(empty($this->summary)){
                // generate summary report
                $this->generateReportSummary();
            }
            // return the summary report to content varaible
            $this->alert['content'] = $this->summary;
        }

        if($type == 'DETAILED' || $store == 'true'){
            if($store != 'false'){
                $this->generateReportDetailesStore();
            }else{
                // generate summary report
                $this->generateReportDetailes($section, $request->key, isset($data) ? $data : false);
            }
            // return the summary report to content varaible
            $this->alert['content'] = $this->detailed;
        }


        if($store == 'true'){
            $this->alert['content'] = $this->saveReport($request->title, $reportType);
        }

    }

    public function generateReportSummary(){
        foreach($this->ports as $port){
            $fullCdrData = [];
            $fullNumbersData = [];
            $fullPortsData = [];
            foreach($this->dates as $date):
                $cdrRecords = collect($this->cdrRecords["port-$port"])->filter(function($item) use($date){
                    return explode(' ', $item->calldate)[0] == $date;
                });
                $totalCalls = $cdrRecords->count();
                $failedCalls = 0;
                $noAnswerCalls = 0;
                $busyCalls = 0;
                $answeredCalls = 0;
                $elseCalls = 0;
                $repeatedCalls = 0;
                $uniqueCalls = 0;
                $billmin = 0;
                $dispositions = [];
                foreach($this->dispositions as $s){ $dispositions[$s] = 0; }
                $dst = [];
                // dd($this->cdrRecords);
                // handle cdr summary
                foreach($cdrRecords as $record){
                    $billmin += $record->billmin;
                    $status = $record->disposition;
                    $index = array_search($status, $this->callStatuses);
                    if(!isset(explode('#', $record->dst)[1])){
                        $dst[] = $record->dst;
                    }
                    if($index !== false){
                        if($status == 'FAILED'){
                            $failedCalls += 1;
                        }elseif($status == 'NO ANSWER'){
                            $noAnswerCalls += 1;
                        }elseif($status == 'BUSY'){
                            $busyCalls += 1;
                        }elseif($status == 'ANSWERED'){
                            $answeredCalls += 1;
                        }
                    }else{
                        $elseCalls += 1;
                    }

                    $dispositions[empty($record->userfield) ? 'NULL' : $record->userfield] += 1;
                
                }
        
                // check repeated numbers
                $dst = array_count_values($dst);
                collect($dst)->map(function($item, $index) use(&$repeatedCalls, &$uniqueCalls){
                    if($item > 1){ $repeatedCalls += $item; }
                    $uniqueCalls += 1;
                }); 


                $fullCdrData[$date] = [
                    'totalCalls' => $totalCalls,
                    'failedCalls' => $failedCalls,
                    'noAnswerCalls' => $noAnswerCalls,
                    'busyCalls' => $busyCalls,
                    'answeredCalls' => $answeredCalls,
                    'elseCalls' => $elseCalls,
                    'repeatedCalls' => $repeatedCalls,
                    'uniqueCalls' => $uniqueCalls,
                    'dispositions' => $dispositions,
                    'billmin' => $billmin
                ];


                $enabledNumbers = 0;
                $disabledNumbers = 0;
                $routesRecords = collect($this->routesRecords["port-$port"])->filter(function($item) use($date){
                    return explode(' ', $item->DateEnabled)[0] == $date || explode(' ', $item->DateDisabled)[0] == $date;
                });
                // $this->routesRecords["port-$port"];
                foreach($routesRecords as $record){
                    if(explode(' ',$record->DateEnabled)[0] == explode(' ', $this->date)[0]){
                        $enabledNumbers += 1;
                    }
        
                    if(!empty($record->DateDisabled)){
                        $disabledNumbers += 1;   
                    }
                }

                $fullNumbersData[$date] = [
                    'enabledNumbers' => $enabledNumbers,
                    'disabledNumbers' => $disabledNumbers,
                ];


                $portsRecords = collect($this->portsRecords["port-$port"])->filter(function($item) use($date){
                    return explode(' ', $item->dateenabled)[0] == $date || explode(' ', $item->datedisabled)[0] == $date;
                });
                $totalPorts = $portsRecords->count();
                $disabledPorts = 0;
                $enabledPorts = 0;
        
                foreach($portsRecords as $record){
                    if(explode(' ',$record->dateenabled)[0] == explode(' ', $this->date)[0]){
                        $enabledPorts += 1;
                    }
        
                    if(!empty($record->datedisabled)){
                        $disabledPorts += 1;   
                    }
                }
                
                $fullPortsData[$date] = [
                    'totalPorts' => $totalPorts,
                    'disabledPorts' => $disabledPorts,
                    'enabledPorts' => $enabledPorts,
                ];

            endforeach;

            $this->summary["cdr-$port"] = $fullCdrData;
            $this->summary["numbers-$port"] = $fullNumbersData;
            $this->summary["ports-$port"] = $fullPortsData;
        }
 
    }

    public function generateReportDetailes($section, $key = false, $data = false){
        $this->alert['pagination'] = [
            'perpage' => (int)$this->perpage,
            'offset' => $this->offset,
            'url' => route('reports.generate.report')
        ];
        $port = $this->ports[0];
        
        if($data):
            // create the variables from the object
            foreach($data["cdr-$port"] as $kkey => $value){
                $$kkey = $value;
            }  
            foreach($data["numbers-$port"] as $kkey => $value){
                $$kkey = $value;
            }  
            foreach($data["ports-$port"] as $kkey => $value){
                $$kkey = $value;
            }  
            // dd($enabledPorts, $data["numbers-$port"], $data["ports-$port"]);
        else:
            if(isset($this->cdrRecords["port-$port"])):
                $totalCalls = $this->cdrRecords["port-$port"];
                $failedCalls = [];
                $noAnswerCalls = [];
                $busyCalls = [];
                $answeredCalls = [];
                $elseCalls = [];
                $repeatedCalls = [];            
                $uniqueCalls = [];
                $dispositions = [];
                foreach($this->dispositions as $s){ $dispositions[$s] = []; }
                $dst = [];
                // handle cdr summary
                foreach($this->cdrRecords["port-$port"] as $record){
                    $status = $record->disposition;
                    $index = array_search($status, $this->callStatuses);
                    if(!isset(explode('#', $record->dst)[1])){
                        $dst[] = $record->dst;
                    }
                    if($index !== false){
                        if($status == 'FAILED'){
                            $failedCalls[] = $record;
                        }elseif($status == 'NO ANSWER'){
                            $noAnswerCalls[] = $record;
                        }elseif($status == 'BUSY'){
                            $busyCalls[] = $record;
                        }elseif($status == 'ANSWERED'){
                            $answeredCalls[] = $record;
                        }
                    }else{
                        $elseCalls[] = $record;
                    }
                    $dispositions[empty($record->userfield) ? 'NULL' : $record->userfield][] = $record;
                }
                $dst = array_count_values($dst);
                $ddd = $dst;
                $dst = collect($dst)->filter(function($item, $index){
                    if($item > 1){ return true; }{ return false; }
                })->toArray(); 
                $repeatedCalls = [];
    
                foreach($dst as $kk => $dd){
                    // dd($dst);
                    // if(!isset(collect($totalCalls)->where('dst', '=' , $kk)[0])){
                    //     continue;
                    // }
                    // $item = collect($totalCalls)->where('dst', '=' , $kk)[0];
                    // $item->repeatedCalls = $dd;
                    $item = [
                        'repeatedCalls' => $dd, 
                        'number' => $kk,
                    ];
                    $repeatedCalls[] = $item;
                }

                foreach($ddd as $kk => $dd){
                    $item = [
                        'repeatedCalls' => $dd, 
                        'number' => $kk,
                    ];
                    $uniqueCalls[] = $item;
                }

            endif;
            // $dst = array_keys($dst);
            // $repeatedCall = collect($totalCalls)->whereIn('dst', $dst);
            // foreach($repeatedCall as $rc){
            //     $isTrue = false;
    
            //     if(!$isTrue){
            //         $repeatedCalls[] = $rc;
            //     }
            // }
            
            if(isset($this->routesRecords["port-$port"])):
                $enabledNumbers = [];
                $disabledNumbers = [];
                foreach($this->routesRecords["port-$port"] as $record){
                    if(explode(' ',$record->DateEnabled)[0] == explode(' ', $this->date)[0]){
                        $enabledNumbers[] = $record;
                    }
    
                    if(!empty($record->DateDisabled)){
                        $disabledNumbers[] = $record;   
                    }
                }
            endif;
    
            if(isset($this->portsRecords["port-$port"])):
                $totalPorts = $this->portsRecords["port-$port"];
                $disabledPorts = 0;
                $enabledPorts = 0;
    
                foreach($this->portsRecords["port-$port"] as $record){
                    if(explode(' ',$record->dateenabled)[0] == explode(' ', $this->date)[0]){
                        $enabledPorts[] = $record;
                    }
    
                    if(!empty($record->datedisabled)){
                        $disabledPorts[] = $record;  
                    }
                }
            endif;
        endif;

        if($key && $key != 'false'){
            $result = $$section;
            if(!isset($result[$key])){
                $result = $result[' '.$key];
            }else{
                $result = $result[$key];
            }
        }else{
            $result = $$section;
        }

        $this->alert['pagination']['total'] = count($result);
        $this->detailed = collect($result)->slice($this->offset, $this->perpage);

    }

    public function generateReportDetailesStore(){
        $data = [];
        foreach($this->ports as $port):
            if(isset($this->cdrRecords["port-$port"])):
                $totalCalls = $this->cdrRecords["port-$port"];
                $failedCalls = [];
                $noAnswerCalls = [];
                $busyCalls = [];
                $answeredCalls = [];
                $elseCalls = [];
                $repeatedCalls = [];            
                $dispositions = [];
                foreach($this->dispositions as $s){ $dispositions[$s] = []; }
                $dst = [];
                // handle cdr summary
                foreach($this->cdrRecords["port-$port"] as $record){
                    $status = $record->disposition;
                    $index = array_search($status, $this->callStatuses);
                    if(!isset(explode('#', $record->dst)[1])){
                        $dst[] = $record->dst;
                    }
                    if($index !== false){
                        if($status == 'FAILED'){
                            $failedCalls[] = $record;
                        }elseif($status == 'NO ANSWER'){
                            $noAnswerCalls[] = $record;
                        }elseif($status == 'BUSY'){
                            $busyCalls[] = $record;
                        }elseif($status == 'ANSWERED'){
                            $answeredCalls[] = $record;
                        }
                    }else{
                        $elseCalls[] = $record;
                    }
                    $dispositions[empty($record->userfield) ? 'NULL' : $record->userfield][] = $record;
                }
                $dst = array_count_values($dst);
                $dst = collect($dst)->filter(function($item, $index){
                    if($item > 1){ return true; }{ return false; }
                })->toArray(); 
                $repeatedCalls = [];

                foreach($dst as $kk => $dd){
                    // dd($dst);
                    // if(!isset(collect($totalCalls)->where('dst', '=' , $kk)[0])){
                    //     continue;
                    // }
                    // $item = collect($totalCalls)->where('dst', '=' , $kk)[0];
                    // $item->repeatedCalls = $dd;
                    $item = [
                        'repeatedCalls' => $dd, 
                        'number' => $kk,
                    ];
                    $repeatedCalls[] = $item;
                }
            endif;
            
            if(isset($this->routesRecords["port-$port"])):
                $enabledNumbers = [];
                $disabledNumbers = [];
                foreach($this->routesRecords["port-$port"] as $record){
                    if(explode(' ',$record->DateEnabled)[0] == explode(' ', $this->date)[0]){
                        $enabledNumbers[] = $record;
                    }

                    if(!empty($record->DateDisabled)){
                        $disabledNumbers[] = $record;   
                    }
                }
            endif;

            if(isset($this->portsRecords["port-$port"])):
                $totalPorts = $this->portsRecords["port-$port"];
                $disabledPorts = [];
                $enabledPorts = [];

                foreach($this->portsRecords["port-$port"] as $record){
                    if(explode(' ',$record->dateenabled)[0] == explode(' ', $this->date)[0]){
                        $enabledPorts[] = $record;
                    }

                    if(!empty($record->datedisabled)){
                        $disabledPorts[] = $record;  
                    }
                }
            endif;

            $this->detailed["cdr-$port"] = [
                'totalCalls' => $totalCalls,
                'failedCalls' => $failedCalls,
                'noAnswerCalls' => $noAnswerCalls,
                'busyCalls' => $busyCalls,
                'answeredCalls' => $answeredCalls,
                'elseCalls' => $elseCalls,
                'repeatedCalls' => $repeatedCalls,
                'dispositions' => $dispositions,
            ];
            $this->detailed["numbers-$port"] = [
                'enabledNumbers' => $enabledNumbers,
                'disabledNumbers' => $disabledNumbers,
            ];
            $this->detailed["ports-$port"] = [
                'totalPorts' => $totalPorts,
                'disabledPorts' => $disabledPorts,
                'enabledPorts' => $enabledPorts,
            ];
        endforeach;

    }

    public function saveReport($title, $type){
        $title = str_replace(':', '_',$title);

        $storeData = json_encode([
            'summary' => $this->summary,
            'detailed' => $this->detailed
        ]);

        $dir = public_path().'/Reports/';
        $fileName = sha1(time()).'__ports__'.$title.'.json';
        if (!is_dir($dir)) {
            mkdir($dir);       
        }
        file_put_contents($dir.$fileName, $storeData);

        $report = [
            'reportType' => $type,
            'title' => str_replace('_' , ':', $title),
            'isLast' => true,
            'dates' => $this->date.'__'.$this->secondDate,
            'report' => $fileName,
            'summary' => $fileName,
        ];  

        $report['compare_hash'] = sha1($storeData . $report['dates'].$type);

        if(Reports::where('compare_hash', '=', $report['compare_hash'])->count() < 1){
            return Reports::create($report);
        }else{
            return ['error' => 'Report Already Exist'];
        }

    }

    public function response(){
        return $this->alert;
    }

    public function summary(){
        return $this->summary;
    }

    public function dates(){
        return $this->date.' __ '.$this->secondDate;
    }
}
