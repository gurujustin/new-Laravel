<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Helpers\SearchQueryGeneratorController;
use App\Http\Controllers\Helpers\GenerateReport;
class ReportsController extends Controller
{
    // route name
    private $r = 'report';
    private $rs = 'reports';  
    private $database = 'Reports';  
    private $title = 'Report';

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
        $this->database = app('App\\'.$this->database);
    }

    /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\Response
         */
    public function index(Request $request)
    {
        $perpage = !empty($request->perpage) ? $request->perpage : 20;
        $offset  = !empty($request->offset) ? $request->offset : 0;
        $search  = !empty($request->search) ? $request->search : false;
        $customSearch  = !empty($request->customSearch) ? $request->customSearch : false;
        $exportCustomSearch  = !empty($request->exportCustomSearch) ? $request->exportCustomSearch : false;
        $order   = !empty($request->order) ? $request->order : false;
        $api     = !empty($request->api) ? true : false;

        if($search){
            if($customSearch){
                
                $query = new SearchQueryGeneratorController($search);
                $this->searchQuery = $query->generate();

                $data  = $this->database::limit($perpage)->offset($offset)->orderBy('updated_at' , 'DESC')->where($this->searchQuery)->get();
                $total = $this->database::limit($perpage)->offset($offset)->orderBy('updated_at' , 'DESC')->where($this->searchQuery)->count();

                if($exportCustomSearch){
                    $exporter = new ExportController($request, $data , $this->database);
                    return $exporter->export();
                }

            }else{
                $search = explode('||' , $search)[0];
                $data = $this->database::limit($perpage)->offset($offset)->orderBy('updated_at' , 'DESC')
                ->where('title' , 'LIKE' , '%'.$search.'%')
                ->orWhere('created_at' , 'LIKE' , '%'.$search.'%')
                ->orWhere('updated_at' , 'LIKE' , '%'.$search.'%')
                ->get();
                $total = $this->database::orderBy('updated_at' , 'DESC')
                ->where('title' , 'LIKE' , '%'.$search.'%')
                ->orWhere('created_at' , 'LIKE' , '%'.$search.'%')
                ->orWhere('updated_at' , 'LIKE' , '%'.$search.'%')
                ->count();
            }

        }elseif($order){
            $data = $this->database::limit($perpage)->offset($offset)->orderBy($order , $request->orderBy)->get();
            $total = $this->database::count();
        }else{
            $data = $this->database::limit($perpage)->offset($offset)->orderBy('updated_at' , 'DESC')->get();
            $total = $this->database::count();
        }

        $data = $data->map(function($item){
            $item->showUrl = route($this->r.'.show' , ['id' => $item->id]);
            $item->editUrl = route($this->r.'.edit' , ['id' => $item->id]);
            $item->deleteUrl = route($this->r.'.delete' , ['id' => $item->id]);
            return $item;
        });

        if(!$api){
            return view('admin.'.$this->rs.'.index')->with(['total' => $total , 'perpage' => $perpage ,
            'offset' => $offset , 'gw' => $data  , 'search' => $search]);
        }

        $currentRoute = \Route::currentRouteName();

        /**
             * all needed data for index
             */
        $all = [
            'gw' => $data ,
            'search' => [
                "searchUrl" => route($this->rs) ,
                "searchPhrase" => $search
            ],
            'urls'  => [
                'addUrl' => route($this->r.'.store')
            ],
            'pagination'  => [
                'total'   => $total ,
                'perpage' => $perpage ,
                'offset'  => $offset,
                'url'     => route($currentRoute)
            ],
        ];

        return response()->json($all , 200);
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


    /**
     * generate report
     */
    public function generateReport(Request $request){
        $report = new GenerateReport($request);

        return response()->json($report->response());
    }
}
