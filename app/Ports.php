<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ports extends Model
{
    protected $table = 'ports';

    protected $fillable = [
        'port', 'dateenabled', 'datedisabled', 'number', 'busy', 'gw_id', 'prefix', 'port_on_gw', 
        'imei', 'op_id'
    ];

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
