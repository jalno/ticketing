<?php
namespace packages\ticketing;
use packages\userpanel\user;
use packages\base\{db, db\dbObject};

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
	const STATUSES = array(
		self::unread,
		self::read,
		self::in_progress,
		self::answered,
		self::closed,
	);
	const PRIORITIES = array(
		self::instantaneous,
		self::important,
		self::ordinary,
	);
	protected $dbTable = "ticketing_tickets";
	protected $primaryKey = "id";
	protected $dbFields = array(
        "create_at" => array("type" => "int", "required" => true),
        "reply_at" => array("type" => "int", "required" => true),
        "title" => array("type" => "text", "required" => true),
		"priority" => array("type" => "int", "required" => true),
		"department" => array("type" => "int", "required" => true),
		"client" => array("type" => "int", "required" => true),
		"operator_id" => array("type" => "int"),
		"status" => array("type" => "int", "required" => true)
    );
	protected $relations = array(
		"message" => array("hasMany", "packages\\ticketing\\ticket_message", "ticket"),
		"params" => array("hasMany", "packages\\ticketing\\ticket_param", "ticket"),
		"client" => array("hasOne", user::class, "client"),
		"department" => array("hasOne", "packages\\ticketing\\department", "department"),
		"operator" => array("hasOne", user::class, "operator_id"),
	);
	protected function preLoad($data){
		if(!isset($data["create_at"])){
			$data["create_at"] = time();
		}
		if(!isset($data["reply_at"])){
			$data["reply_at"] = time();
		}
		return $data;
	}

	protected $tmpmessages = array();
	protected $tmparams = array();
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
				"name" => $name,
				"value" => $value
			));
		}else{
			$param->value = $value;
		}

		if(!$this->id or $this->isNew){
			$this->tmparams[$name] = $param;
		}else{
			$param->ticket = $this->id;
			return $param->save();
		}
	}
	public function save($data = null){
		if(($return = parent::save($data))){
			foreach($this->tmparams as $param){
				$param->ticket = $this->id;
				$param->save();
			}
			$this->tmparams = array();
			foreach($this->tmpmessages as $message){
				$message->ticket = $this->id;
				$message->save();
			}
			$this->tmpmessages = array();
		}
		return $return;
	}
	public function delete(){
		db::join("ticketing_tickets_msgs msg", "msg.id=ticketing_files.message", "LEFT");
		db::where("msg.ticket", $this->id);
		$files = db::get("ticketing_files", null, "ticketing_files.*");
		foreach($files as $file){
			$file = new ticket_file($file);
			$file->delete();
		}
		parent::delete();
	}
	public function getMessageCount(): int {
		$message = new ticket_message();
		$message->where("ticket", $this->id);
		return max($message->count() - 1, 0);
	}
	public function hasUnreadMessage(): bool {
		db::join("ticketing_tickets", "ticketing_tickets.id=ticketing_tickets_msgs.ticket", "INNER");
		db::joinWhere("ticketing_tickets", "ticketing_tickets.id", $this->id);
		$message = new ticket_message();
		$message->where("ticketing_tickets_msgs.status", ticket_message::unread);
		$message->where("ticketing_tickets_msgs.user", "ticketing_tickets.client", "!=");
		return $message->has();
	}
}
