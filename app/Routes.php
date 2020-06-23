<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Routes extends Model
{
    protected $table = 'routes';

    protected $fillable = [
        'id' , 'number', 'port', 'DateEnabled', 'DateDisabled'
    ];

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
