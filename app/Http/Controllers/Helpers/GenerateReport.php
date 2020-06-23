<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CDR;
use App\Routes;
use App\Ports;
use DB;

class GenerateReport extends Controller
{
    protected $alert = [];
    protected $date = '';
    protected $secondDate = '';

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

    public function __construct($request){
        $this->date = str_replace('+' , ' ',$request->date);
        $this->secondDate = !empty($request->endDate) ? str_replace('+' , ' ',$request->endDate) : date('Y-m-d', strtotime($this->date) + 24*60*60);
       
        $perpage = !empty($request->perpage) && $request->perpage > 0 ? $request->perpage : 1000000000000000;
        $offset = !empty($request->offset) ? $request->offset : 0;

        $this->perpage = $perpage;
        $this->offset = $offset;

        $type = !empty($request->type) ? $request->type : 'SUMMARY';
        $action = !empty($request->action) ? $request->action : 'GENERATE';
        $target = !empty($request->target) ? $request->target : 'ALL';
        $section = !empty($request->section) ? $request->section : 'ALL';
        $store = !empty($request->store) ? $request->store : 'false';
        
        // cdr data
        if($target == 'ALL' || $target == 'CDR'){
            $this->cdrRecords = CDR::
            where([['calldate', '>=', $this->date], ['calldate', '<=', $this->secondDate]])
            ->whereNotNull('dst')->whereNotNull('clid')->where('dst', '!=' , '')->where('clid', '!=' , '')
            // ->limit($perpage)
            // ->offset($offset)
            ->get();

            $this->cdrRecordsCount = CDR::
            where([['calldate', '>=', $this->date], ['calldate', '<=', $this->secondDate]])
            ->whereNotNull('dst')->whereNotNull('clid')->where('dst', '!=' , '')->where('clid', '!=' , '')
            ->count();
        }


        // routes data
        if($target == 'ALL' || $target == 'ROUTES'){
            $this->routesRecords = Routes::
            where([['DateEnabled', '>=', $this->date], ['DateEnabled', '<=', $this->secondDate]])
            ->orWhere([['DateDisabled', '>=', $this->date], ['DateDisabled', '<=', $this->secondDate]])
            // ->offset($offset)
            // ->limit($perpage)
            ->get();

            $this->routesRecordsCount = Routes::
            where([['DateEnabled', '>=', $this->date], ['DateEnabled', '<=', $this->secondDate]])
            ->orWhere([['DateDisabled', '>=', $this->date], ['DateDisabled', '<=', $this->secondDate]])
            ->count();
        }

        // ports data
        if($target == 'ALL' || $target == 'PORTS'){
            $this->portsRecords = Ports::
            where([['dateenabled', '>=', $this->date], ['dateenabled', '<=', $this->secondDate]])
            ->orWhere([['datedisabled', '>=', $this->date], ['datedisabled', '<=', $this->secondDate]])
            // ->offset($offset)
            // ->limit($perpage)
            ->get();

            $this->portsRecordscount = Ports::
            where([['dateenabled', '>=', $this->date], ['dateenabled', '<=', $this->secondDate]])
            ->orWhere([['datedisabled', '>=', $this->date], ['datedisabled', '<=', $this->secondDate]])
            ->count();

        }

        if($type == 'SUMMARY' || $store == 'true'){
            // generate summary report
            $this->generateReportSummary();
            // return the summary report to content varaible
            $this->alert['content'] = $this->summary;
        }

        if($type == 'DETAILED' || $store == 'true'){
            // generate summary report
            $this->generateReportDetailes($section, $store);
            // return the summary report to content varaible
            $this->alert['content'] = $this->detailed;
        }


        if($store == 'true'){
            dd('store var');
        }

    }

    public function generateReportSummary(){
        $totalCalls = $this->cdrRecords->count();
        $failedCalls = 0;
        $noAnswerCalls = 0;
        $busyCalls = 0;
        $answeredCalls = 0;
        $elseCalls = 0;
        $repeatedCalls = 0;
        $dst = [];
        // dd($this->cdrRecords);
        // handle cdr summary
        foreach($this->cdrRecords as $record){
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
        }

        // check repeated numbers
        $dst = array_count_values($dst);
        collect($dst)->map(function($item, $index) use(&$repeatedCalls){
            if($item > 1){ $repeatedCalls += $item; }
        }); 


        $this->summary['cdr'] = [
            'totalCalls' => $totalCalls,
            'failedCalls' => $failedCalls,
            'noAnswerCalls' => $noAnswerCalls,
            'busyCalls' => $busyCalls,
            'answeredCalls' => $answeredCalls,
            'elseCalls' => $elseCalls,
            'repeatedCalls' => $repeatedCalls,
        ];

        $enabledNumbers = 0;
        $disabledNumbers = 0;

        foreach($this->routesRecords as $record){
            if(explode(' ',$record->DateEnabled)[0] == explode(' ', $this->date)[0]){
                $enabledNumbers += 1;
            }

            if(!empty($record->DateDisabled)){
                $disabledNumbers += 1;   
            }
        }

        $this->summary['numbers'] = [
            'enabledNumbers' => $enabledNumbers,
            'disabledNumbers' => $disabledNumbers,
        ];


        $totalPorts = $this->portsRecords->count();
        $disabledPorts = 0;
        $enabledPorts = 0;

        foreach($this->portsRecords as $record){
            if(explode(' ',$record->dateenabled)[0] == explode(' ', $this->date)[0]){
                $enabledPorts += 1;
            }

            if(!empty($record->datedisabled)){
                $disabledPorts += 1;   
            }
        }
        $this->summary['ports'] = [
            'totalPorts' => $totalPorts,
            'disabledPorts' => $disabledPorts,
            'enabledPorts' => $enabledPorts,
        ];
    }

    public function generateReportDetailes($section, $store){
        $this->alert['pagination'] = [
            'perpage' => (int)$this->perpage,
            'offset' => $this->offset,
            'url' => route('reports.generate.report')
        ];

        $totalCalls = $this->cdrRecords;
        $failedCalls = [];
        $noAnswerCalls = [];
        $busyCalls = [];
        $answeredCalls = [];
        $elseCalls = [];
        $repeatedCalls = [];
        $dst = [];
        // handle cdr summary
        foreach($this->cdrRecords as $record){
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

        // $dst = array_keys($dst);
        // $repeatedCall = collect($totalCalls)->whereIn('dst', $dst);
        // foreach($repeatedCall as $rc){
        //     $isTrue = false;

        //     if(!$isTrue){
        //         $repeatedCalls[] = $rc;
        //     }
        // }

        $enabledNumbers = [];
        $disabledNumbers = [];

        foreach($this->routesRecords as $record){
            if(explode(' ',$record->DateEnabled)[0] == explode(' ', $this->date)[0]){
                $enabledNumbers[] = $record;
            }

            if(!empty($record->DateDisabled)){
                $disabledNumbers[] = $record;   
            }
        }

        $totalPorts = $this->portsRecords;
        $disabledPorts = 0;
        $enabledPorts = 0;

        foreach($this->portsRecords as $record){
            if(explode(' ',$record->dateenabled)[0] == explode(' ', $this->date)[0]){
                $enabledPorts[] = $record;
            }

            if(!empty($record->datedisabled)){
                $disabledPorts[] = $record;  
            }
        }



        $result = $$section;

        $this->alert['pagination']['total'] = count($result);
        $this->detailed = collect($result)->slice($this->offset, $this->perpage);

    }

    public function response(){
        return $this->alert;
    }
}
