<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reports extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'compare_hash', 'isLast', 'title', 'report', 'summary', 'reportType', 'dates'
    ];
    
    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
