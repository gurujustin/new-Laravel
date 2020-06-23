<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CDR extends Model
{
    protected $table = 'cdr';

    protected $fillable = [
        'calldate' , 'clid' , 'src' , 'dst', 'dcontext', 'channel', 'dstchannel', 'lastapp', 'lastdata', 'duration', 
        'billsec' , 'start', 'answer', 'end', 'disposition', 'amaflags', 'accountcode', 'userfield', 'uniqueid', 'port',
        'billmin'
    ];

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
