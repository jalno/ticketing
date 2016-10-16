<?php
namespace packages\ticketing;
use \packages\base\db\dbObject;
class ticket extends dbObject{
	const unread = 1;
	const read = 2;
	const in_progress = 3;
	const answered = 4;
	const closed = 5;
	const instantaneous = 1;
	const important = 2;
	const ordinary = 3;
	const canSendMessage = 0;
	protected $dbTable = "ticketing_tickets";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'create_at' => array('type' => 'int', 'required' => true),
        'reply_at' => array('type' => 'int', 'required' => true),
        'title' => array('type' => 'text', 'required' => true),
		'priority' => array('type' => 'int', 'required' => true),
		'department' => array('type' => 'int', 'required' => true),
		'client' => array('type' => 'int', 'required' => true),
		'status' => array('type' => 'int', 'required' => true)
    );
	protected $relations = array(
		'message' => array('hasMany', 'packages\\ticketing\\ticket_message', 'ticket'),
		'params' => array('hasMany', 'packages\\ticketing\\ticket_param', 'ticket'),
		'client' => array('hasOne', 'packages\\userpanel\\user', 'client'),
		'department' => array('hasOne', 'packages\\ticketing\\department', 'department')
	);
	protected function preLoad($data){
		if(!isset($data['create_at'])){
			$data['create_at'] = time();
		}
		if(!isset($data['reply_at'])){
			$data['reply_at'] = time();
		}
		return $data;
	}

	protected $tmpmessages = array();
	protected function addMessage($messagedata){
		$message = new ticket_message($messagedata);
		if ($this->isNew){
			$this->tmpmessages[] = $message;
			return true;
		}else{
			$message->ticket = $this->id;
			$return = $message->save();
			if(!$return){
				return false;
			}
			return $return;
		}
	}
	public function param($name){
		if(!$this->id){
			return(isset($this->tmparams[$name]) ? $this->tmparams[$name]->value : null);
		}else{
			foreach($this->params as $param){
				if($param->name == $name){
					return $param->value;
				}
			}
			return false;
		}
	}
	public function setParam($name, $value){
		$param = false;
		foreach($this->params as $p){
			if($p->name == $name){
				$param = $p;
				break;
			}
		}
		if(!$param){
			$param = new ticket_param(array(
				'name' => $name,
				'value' => $value
			));
		}else{
			$param->value = $value;
		}

		if(!$this->id){
			$this->tmparams[$name] = $param;
		}else{
			$param->ticket = $this->id;
			return $param->save();
		}
	}
	public function save($data = null){
		if(($return = parent::save($data))){
			foreach($this->tmpmessages as $message){
				$message->ticket = $this->id;
				$message->save();
			}
			$this->tmpmessages = array();
		}
		return $return;
	}
}
