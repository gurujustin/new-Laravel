<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CDR;
use App\Routes;
use App\Ports;
use DB;
class NumbersController extends Controller
{
    // route name
    private $r = 'number';
    private $rs = 'numbers';  
    private $database = 'CDR';  
    private $title = 'Numbers';

    private $insertRules = [
        'compare_hash' => 'required',
        'title' => 'required',
        'report' => 'required',
        'summary' => 'required',
    ];  
    private $updateRules = [
        'compare_hash' => 'required',
        'title' => 'required',
        'report' => 'required',
        'summary' => 'required',
    ];

    private $searchQuery;

    public function __construct(){
        // $this->database = app('App\\'.$this->database);
    }

    /**
     * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $target = !empty($request->target) ? $request->target : false;
        $type = !empty($request->type) ? $request->type : 'SUMMARY';
        $perpage = !empty($request->perpage) ? $request->perpage : 20;
        $topCalledNumbersCount = !empty($request->topCalledNumbersCount) ? $request->topCalledNumbersCount : 20;
        $topCallersNumbersCount = !empty($request->topCallersNumbersCount) ? $request->topCallersNumbersCount : 20;
        $offset  = !empty($request->offset) ? $request->offset : 0;
        $search  = !empty($request->search) ? $request->search : '';
        $searchCallers  = !empty($request->searchCallers) ? $request->searchCallers : '';
        $searchNumbers  = !empty($request->searchNumbers) ? $request->searchNumbers : '';
        $api     = !empty($request->api) ? $request->api : false;
        $dateFrom   = str_replace('+' , ' ', $request->dateFrom);
        $dateTo   = str_replace('+' , ' ', $request->dateTo);
        $dateFromCallers   = str_replace('+' , ' ', $request->dateFromCallers);
        $dateToCallers   = str_replace('+' , ' ', $request->dateToCallers);
        $dateFromNumbers  = str_replace('+' , ' ', $request->dateFromNumbers);
        $dateToNumbers  = str_replace('+' , ' ', $request->dateToNumbers);
        $dateQuery = !empty($dateFrom) && !empty($dateTo) ? "BETWEEN '$dateFrom' AND '$dateTo'" : '';
        $dateQueryCallers = !empty($dateFromCallers) && !empty($dateToCallers) ? "BETWEEN '$dateFromCallers' AND '$dateToCallers'" : '';
        $dateQueryNumbers = !empty($dateFromNumbers) && !empty($dateToNumbers) ? "BETWEEN '$dateFromNumbers' AND '$dateToNumbers'" : '';

        if(!$target || $target == 'uniqueNumbers'){
            $uniqueNumbers = CDR::select('dst')->where('dst' , 'LIKE' , "%$search%")->distinct('dst');
            if($type == 'SUMMARY'){
                $uniqueNumbers = $uniqueNumbers->count();
            }elseif($type == 'DETAILED'){
                $countuniqueNumbers = $uniqueNumbers->count();
                $uniqueNumbers = $uniqueNumbers->offset($offset)->limit($perpage)->get();
            }
        }

        /**
         * get top called data
         */

        if(!$target || $target == 'topCalled'){
            if($type == 'SUMMARY'){
                $ddDateQuery = !empty($dateQuery) ? "AND `calldate` $dateQuery" : '';
                $query = "SELECT `dst`, count(*) as `repeated` FROM `cdr` 
                WHERE `dst` LIKE '%$search%' AND `dst` != '' $ddDateQuery
                GROUP BY `dst`
                ORDER BY `repeated` DESC 
                LIMIT $topCalledNumbersCount";
                $topCalled = DB::select($query);

                $query = "SELECT `calldate` FROM `cdr` 
                WHERE `dst` LIKE '%$search%' AND `dst` != '' $ddDateQuery
                ORDER BY `calldate` DESC 
                LIMIT 1";
                $lastValue = DB::select($query)[0];

                $query = "SELECT `calldate` FROM `cdr` 
                WHERE `dst` LIKE '%$search%' AND `dst` != '' $ddDateQuery
                ORDER BY `calldate` ASC 
                LIMIT 1";
                $firstValue = DB::select($query)[0];
                
                $firstDate = strtotime($firstValue->calldate); // or your date as well
                $secondDate = strtotime($lastValue->calldate); // or your date as well
                $datediff = $secondDate - $firstDate;
                $topCalledAverage = round($datediff / (60 * 60 * 24));

            }elseif($type == 'DETAILED'){
                $ddDateQuery = !empty($dateQuery) ? "AND `calldate` $dateQuery" : '';
                $query = "SELECT * FROM `cdr` 
                WHERE `dst` = '$search' $ddDateQuery
                ORDER BY `calldate` DESC 
                LIMIT $offset,$perpage";
                $counttopCalled = DB::select("SELECT COUNT(*) as `records` FROM `cdr` 
                WHERE `dst` = '$search' $ddDateQuery")[0];
                $topCalled = DB::select($query);

                $pagination = [
                    'total' => $counttopCalled->records,
                    'perpage' => $perpage,
                    'offset' => $offset,
                ];

            }
        }

        // get top callers data
        if(!$target || $target == 'topCallers'){
            if($type == 'SUMMARY'){
                $ddDateQuery = !empty($dateQueryCallers) ? "AND `calldate` $dateQueryCallers" : '';
                $query = "SELECT `src`, count(*) as `repeated` FROM `cdr` 
                WHERE `src` LIKE '%$searchCallers%' AND `src` != '' $ddDateQuery
                GROUP BY `src`
                ORDER BY `repeated` DESC 
                LIMIT $topCallersNumbersCount";
                $topCallers = DB::select($query);

                $query = "SELECT `calldate` FROM `cdr` 
                WHERE `src` LIKE '%$searchCallers%' AND `src` != '' $ddDateQuery
                ORDER BY `calldate` DESC 
                LIMIT 1";
                $lastValue = DB::select($query)[0];

                $query = "SELECT `calldate` FROM `cdr` 
                WHERE `src` LIKE '%$searchCallers%' AND `src` != '' $ddDateQuery
                ORDER BY `calldate` ASC 
                LIMIT 1";
                $firstValue = DB::select($query)[0];
                
                $firstDate = strtotime($firstValue->calldate); // or your date as well
                $secondDate = strtotime($lastValue->calldate); // or your date as well
                $datediff = $secondDate - $firstDate;
                $topCallersAverage = round($datediff / (60 * 60 * 24));

            }elseif($type == 'DETAILED'){
                $ddDateQuery = !empty($dateQuery) ? "AND `calldate` $dateQuery" : '';
                $query = "SELECT * FROM `cdr` 
                WHERE `src` = '$searchCallers' $ddDateQuery
                ORDER BY `calldate` DESC 
                LIMIT $offset,$perpage";
                $counttopCalled = DB::select("SELECT COUNT(*) as `records` FROM `cdr` 
                WHERE `src` = '$searchCallers' $ddDateQuery")[0];
                $topCallers = DB::select($query);

                $pagination = [
                    'total' => $counttopCalled->records,
                    'perpage' => $perpage,
                    'offset' => $offset,
                ];

            }
        }

        // get the enabled and disabled data
        if(!$target || $target == 'enabledNumbers'){
            // get and store records
            if(!empty($searchNumbers)){
                $ports = Ports::where('port' , '=', $searchNumbers)->get();
            }else{
                $ports = Ports::all();
            }

            $enabledNubmersNone = [];
            $ports->map(function($item) use(&$enabledNubmersNone, $dateQueryNumbers){
                $ddDateQuery = !empty($dateQueryNumbers) ? "AND `DateEnabled` $dateQueryNumbers OR `port` = '$item->port' AND `DateDisabled` $dateQueryNumbers" : '';
                $query = "SELECT * FROM `routes` 
                WHERE `port` = '$item->port' $ddDateQuery
                ORDER BY `id` DESC";
                $enabledNubmersNone[] = DB::select($query);
            });

            // handle the pretty type records 
            $enabledNumbers = [];

            if($type == 'SUMMARY'){
                foreach($enabledNubmersNone as $numbersPortData){
                    $data = (object)[
                        'port' => isset($numbersPortData[0]->port) ? $numbersPortData[0]->port : '',
                        'enabled' => 0,
                        'disabled' => 0,
                    ];
                    foreach($numbersPortData as $numberPortData){
                        if(!empty($numberPortData->DateEnabled)){
                            $data->enabled += 1;
                        }
    
                        if(!empty($numberPortData->DateDisabled)){
                            $data->disabled += 1;
                        }
                    }
                    $enabledNumbers[$data->port] = $data;
                }

            }elseif($type == 'DETAILED'){
                $enabledNumbers = $enabledNubmersNone[0];
                $pagination = [
                    'total' => count($enabledNumbers),
                    'perpage' => $perpage,
                    'offset' => $offset,
                ];
                $enabledNumbers = collect($enabledNumbers)->slice($offset, $perpage);
            }
        }

        if(!$api){
            $data = [
                'uniqueNumbers' => $uniqueNumbers,
                'topCalled' => $topCalled,
                'topCalledAverage' => $topCalledAverage,
                'topCallers' => $topCallers,
                'topCallersAverage' => $topCallersAverage,
                'enabledNumbers' => $enabledNumbers,
            ];

            return view('admin.'.$this->rs.'.index')->with(['gw' => $data, 'perpage' => $perpage ,
            'offset' => $offset , 'gw' => $data  , 'search' => $search]);
        }

        $average = $target.'Average';

        return response()->json([$target => $$target,  $average => isset($$average) ? $$average : 0, 'pagination' => isset($pagination) ? $pagination : []]);

        dd($topCalled);
    }

    /**
         * Store a newly created resource in storage.
        *
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        */
    public function store(Request $request)
    {   
        $validator = \Validator::make($request->all() , $this->insertRules);

        /**
         * check if uniquer
        */
        $item = $this->database::create($request->all());
        $id = $item->id;
        $item->showUrl = route($this->r.'.show' , ['id' => $id]);
        $item->editUrl = route($this->r.'.edit' , ['id' => $id]);
        $item->updateUrl = route($this->r.'.update' , ['id' => $id]);
        $item->deleteUrl = route($this->r.'.delete' , ['id' => $id]);
        return response()->json($item , 200);
    }

    /**
         * get update form
        */
    public function edit($id){
        $item = $this->database::find($id);

        if($item->count() > 0){
            $item->showUrl = route($this->r.'.show' , ['id' => $id]);
            $item->editUrl = route($this->r.'.edit' , ['id' => $id]);
            $item->updateUrl = route($this->r.'.update' , ['id' => $id]);
            $item->deleteUrl = route($this->r.'.delete' , ['id' => $id]);
            return response()->json($item , 200);
        }else{
            return response()->json(['error' => $this->title.' Not Found'] , 400);
        }
    }

    public function show($id, Request $request){
        $report = $this->database::find($id);
        $reportData = json_decode(file_get_contents(public_path().'/Reports/'.$report->report));
        return response()->json($reportData->summary);
    }

    /**
         * update data
        */
    public function update($id , Request $request){
        $validator = \Validator::make($request->all() , $this->updateRules);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()[0]] , 400);
        } 

        /**
         * check if uniquer
        */
        $item = $this->database::where('name' , '=' , $request->value)->get();

        if($item->count() < 1 || $item[0]->id != $id){
            $item = $this->database::find($id);
            $item->update($request->all());
            $item->showUrl = route($this->r.'.show' , ['id' => $id]);
            $item->editUrl = route($this->r.'.edit' , ['id' => $id]);
            $item->updateUrl = route($this->r.'.update' , ['id' => $id]);
            $item->deleteUrl = route($this->r.'.delete' , ['id' => $id]);
            return response()->json($item , 200);
        }

        return response()->json(['error' => $this->title.' Already Exist'] , 400);
    }

    /**
         * remove vendor
        */
    public function destroy($id){
        $this->database::find($id)->delete();
        return response()->json(['success' => $this->title.' Deleted Successfully'] , 200);
    }

    /**
     * export all 
    */
    public function exportAll(){
        $users = collect($this->database::all())->map(function($item){
            $item = json_decode(json_encode($item) , true);
            return $item;
        });
        $data  = json_encode($users);

        $files = glob(public_path()."/upload/json/*"); // get all file names

        foreach($files as $file){ // iterate files
            if(is_file($file)){
                unlink($file); // delete file
            }
        }

        $file = time() . '_'.$this->title.'.json';
        
        $destinationPath=public_path()."/upload/json/";
        
        // create the direction
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        
        \File::put($destinationPath.$file,$data);

        return response()->download($destinationPath.$file);
    }

    /**
     * get ports data
    */
    public function getPorts(){
        return response()->json(Ports::all());
    }

    /**
     * import 
    */
    public function importJson(Request $request){
        $allData = json_decode(file_get_contents($request->file('file')->getRealPath()) , true);
        foreach($allData as $data){
            $this->database::create($data);
        }

        return redirect()->back();
    }

    /**
     * to array
    */
    public function toArray($string){
        try{
            return json_decode($string , true);
        }catch(\Exception $e){
            return $string;
        }
    }

    /**
     * to Json
    */
    public function toJson($string){
        try{
            return json_encode($string);
        }catch(\Exception $e){
            return $string;
        }
    }
}
