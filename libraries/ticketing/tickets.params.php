<?php
namespace packages\ticketing;
use \packages\base\db\dbObject;
class ticket_param extends dbObject{
	protected $dbTable = "ticketing_tickets_params";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'ticket' => array('type' => 'int', 'required' => true),
        'name' => array('type' => 'text', 'required' => true),
        'value' => array('type' => 'text', 'required' => true)
    );
}
