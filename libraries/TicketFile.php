<?php

namespace packages\ticketing;

use packages\base\DB;
use packages\base\DB\DBObject;
use packages\base\Packages;

class TicketFile extends DBObject
{
    protected $dbTable = 'ticketing_files';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'message' => ['type' => 'int', 'required' => true],
        'name' => ['type' => 'text', 'required' => true],
        'size' => ['type' => 'int', 'required' => true],
        'path' => ['type' => 'text', 'required' => true],
    ];

    public function delete()
    {
        DB::where('path', $this->path);
        DB::where('id', $this->id, '!=');
        if (!DB::has($this->dbTable)) {
            @unlink(Packages::package('ticketing')->getFilePath('storage/'.$this->path));
        }

        return parent::delete();
    }
}
