<?php
namespace packages\ticketing;
use \packages\base\db;
use \packages\base\db\dbObject;
use \packages\base\packages;
class ticket_file extends dbObject{
	protected $dbTable = "ticketing_files";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'message' => array('type' => 'int', 'required' => true),
        'name' => array('type' => 'text', 'required' => true),
        'size' => array('type' => 'int', 'required' => true),
        'path' => array('type' => 'text', 'required' => true)
    );
	public function delete(){
		db::where("path", $this->path);
		db::where("id", $this->id, "!=");
		if(!db::has($this->dbTable)){
			@unlink(packages::package('ticketing')->getFilePath('storage/'.$this->path));
		}
		parent::delete();
	}
}
