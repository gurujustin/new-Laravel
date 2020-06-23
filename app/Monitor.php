<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Monitor extends Model
{
    protected $table = 'monitor';

    protected $fillable = ['wait' , 'status', 'isRunning', 'isError'];

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
