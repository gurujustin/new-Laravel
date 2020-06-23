<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchQueryGeneratorController extends Controller
{
    public function __construct($search){
        $this->search = $search;
    }

    public function generate(){
        $search = $this->search;
        if(!empty($search)){
            $this->searchQuery = collect(explode('||',$search))->map(function($item){
                $it = explode('__' , $item);
                if($it[2] == 'LIKE'){
                    return [    
                        $it[0] , $it[2] , '%'.$it[1].'%'
                    ];
                }else{
                    return [    
                        $it[0] , $it[2] , $it[1]
                    ];
                }
            });
    
            $q = [];
            foreach($this->searchQuery as $k){
                $q[] = $k;
            }
    
            $this->searchQuery = $q;
        }else{
            $this->searchQuery = [];
        }

        return $this->searchQuery;
    }
}
