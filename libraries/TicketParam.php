<?php
namespace packages\ticketing;
use \packages\base\DB\DBObject;
class TicketParam extends DBObject{
	protected $dbTable = "ticketing_tickets_params";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'ticket' => array('type' => 'int', 'required' => true),
        'name' => array('type' => 'text', 'required' => true),
        'value' => array('type' => 'text', 'required' => true)
    );
	protected $relations = array(
		'ticket' => array('hasOne', 'packages\\ticketing\\ticket', 'ticket')
	);
}
