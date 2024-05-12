<?php
namespace packages\ticketing;
use \packages\base\DB;
use \packages\base\DB\DBObject;
use \packages\base\Packages;
class TicketFile extends DBObject{
	protected $dbTable = "ticketing_files";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'message' => array('type' => 'int', 'required' => true),
        'name' => array('type' => 'text', 'required' => true),
        'size' => array('type' => 'int', 'required' => true),
        'path' => array('type' => 'text', 'required' => true)
    );
	public function delete(){
		DB::where("path", $this->path);
		DB::where("id", $this->id, "!=");
		if(!DB::has($this->dbTable)){
			@unlink(Packages::package('ticketing')->getFilePath('storage/'.$this->path));
		}
		return parent::delete();
	}
}
