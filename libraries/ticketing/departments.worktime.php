<?php
namespace packages\ticketing\department;
use \packages\base\db\dbObject;
class worktime extends dbObject{
	const saturday = 6;
	const sunday = 7;
	const monday = 1;
	const tuesday = 2;
	const wednesday = 3;
	const thursday = 4;
	const friday = 5;
	protected $dbTable = "ticketing_departments_worktimes";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'department' => array('type' => 'int', 'required' => true),
        'day' => array('type' => 'int', 'required' => true),
        'time_start' => array('type' => 'int'),
        'time_end' => array('type' => 'int'),
        'message' => array('type' => 'text')
    );
	protected $relations = array(
		'department' => array('hasOne', 'packages\\ticketing\\department', 'department')
	);
}
