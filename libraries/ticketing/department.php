<?php
namespace packages\ticketing;
use \packages\base\db\dbObject;
class department extends dbObject{
	protected $dbTable = "ticketing_departments";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'title' => array('type' => 'text', 'required' => true)
    );
}
