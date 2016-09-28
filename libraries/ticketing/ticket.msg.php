<?php
namespace packages\ticketing;
use \packages\base\db\dbObject;
use \packages\userpanel\user;
use \packages\userpanel\user_option;
use \packages\userpanel\usertype_option;
class ticket_message extends dbObject{
	protected $dbTable = "ticketing_tickets_msgs";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'ticket' => array('type' => 'int', 'required' => true),
        'date' => array('type' => 'int', 'required' => true),
		'user' => array('type' => 'int', 'required' => true),
        'text' => array('type' => 'text', 'required' => true),
        'format' => array('type' => 'text', 'required' => true),
		'status' => array('type' => 'int', 'required' => true)
    );
	protected $relations = array(
		'user' => array('hasOne', 'packages\\userpanel\\user', 'user')
	);
	protected function preLoad($data){
		if(!isset($data['format'])){
			$user = user::where('id', $data['user'])->getOne();
			$data['format'] = $user->option('ticketing_editor');
		}

		return $data;
	}
}
