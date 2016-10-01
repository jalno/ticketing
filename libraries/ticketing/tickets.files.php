<?php
namespace packages\ticketing;
use \packages\base\db\dbObject;
class ticket_file extends dbObject{
	protected $dbTable = "ticketing_files";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'message' => array('type' => 'int', 'required' => true),
        'name' => array('type' => 'text', 'required' => true),
        'size' => array('type' => 'int', 'required' => true),
        'path' => array('type' => 'text', 'required' => true)
    );
}
