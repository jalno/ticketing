<?php
namespace packages\ticketing;

use packages\base\{db, db\dbObject, Options};
use packages\userpanel\{User, Authentication};

class ticket extends dbObject
{
	const unread = 1;
	const read = 2;
	const in_progress = 3;
	const answered = 4;
	const closed = 5;

	const instantaneous = 1;
	const important = 2;
	const ordinary = 3;

	const canSendMessage = 0;

	const SEND_NOTIFICATION_USER_OPTION_NAME = "ticketing_send_notification";

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
	public static function sendNotificationOnSendTicket(?User $user = null): ?bool {
		if ($user) {
			$res = $user->getOption(Ticket::SEND_NOTIFICATION_USER_OPTION_NAME);
			if (!is_null($res)) {
				return $res;
			}
		}
		return Options::get("packages.ticketing.send_notification_on_send_ticket");
	}

	public static function countUnreadTicketCountByUser(?User $user = null): int {
		if (!$user) {
			$user = Authentication::getUser();
		}
		$accessedDeparments = array();
		$types = $user->childrenTypes();
		if ($types) {
			foreach ((new Department)->get() as $department) {
				if ($department->users) {
					if (in_array($user->id, $department->users)) {
						$accessedDeparments[] = $department->id;
					}
				} else {
					$accessedDeparments[] = $department->id;
				}
			}
			if (!$accessedDeparments){
				return 0;
			}
		}
		$ticket = new Ticket();
		$count = 0;
		if ($types) {
			db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "INNER");
			$ticket->where("userpanel_users.type", $types, "IN");
			$ticket->where("ticketing_tickets.status", array(self::unread, self::read, self::in_progress), "IN");
			$ticket->where("ticketing_tickets.department", $accessedDeparments, "IN");
			return $ticket->count();
		}
		db::join("ticketing_tickets_msgs", "ticketing_tickets_msgs.ticket=ticketing_tickets.id", "INNER");
		db::joinWhere("ticketing_tickets_msgs", "ticketing_tickets_msgs.status", Ticket_message::unread);
		$ticket->where("ticketing_tickets.client", $user->id);
		return $ticket->count();
	}

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

	/**
	 * @return ILabel[]
	 */
	public function getLabels(): array
	{
		DB::join('ticketing_tickets_labels', 'ticketing_tickets_labels.label_id=ticketing_labels.id', 'inner');

		$query = new Label();
		$query->where('ticketing_labels.status', Label::ACTIVE);
		$query->where('ticketing_tickets_labels.ticket_id', $this->id);

		return $query->get(null, 'ticketing_labels.*');
	}

	/**
	 * @param int[] $ids
	 * 
	 * @return ILabel[] ticket labels
	 */
	public function setLabels(array $ids): array
	{
		$labels = [];
		$mustBeDeleted = [];
		$mustBeAdded = [];

		$query = DB::where('ticketing_tickets_labels.ticket_id', $this->id);
		$existsLabelsIds = array_column($query->get('ticketing_tickets_labels', null, 'ticketing_tickets_labels.label_id'), 'label_id');

		if ($ids) {
			$query = new Label();
			$query->where('id', $ids, 'in');
			$query->where('status', Label::ACTIVE);
			$labels = $query->get(null, ['id', 'title', 'color']);

			$mustBeAdded = array_values(array_diff($ids, $existsLabelsIds));
			$mustBeDeleted = array_values(array_diff($existsLabelsIds, $ids));
		} else {
			$mustBeDeleted = $ids;
		}

		if ($mustBeDeleted or !$ids) {
			$query = DB::where('ticketing_tickets_labels.ticket_id', $this->id);
			if ($mustBeDeleted) {
				$query->where('ticketing_tickets_labels.label_id', $mustBeDeleted, 'in');
			}
			$query->delete('ticketing_tickets_labels');
		}

		if ($mustBeAdded) {
			DB::insertMulti('ticketing_tickets_labels', array_map(fn ($id) => ['label_id' => $id, 'ticket_id' => $this->id], $mustBeAdded));
		}

		return $labels;
	}

	/**
	 * @param int[] $ids
	 * 
	 * @return ILabel[] deleted labels
	 */
	public function deleteLabels(array $ids): array
	{
		$query = new Label();
		$query->where('id', $ids, 'in');
		$labels = $query->get(null, ['id', 'title', 'color']);

		$ids = array_column($labels, 'id');

		$query = DB::where('ticketing_tickets_labels.ticket_id', $this->id);
		if ($ids) {
			$query->where('ticketing_tickets_labels.label_id', $ids, 'in');
		}
		$query->delete('ticketing_tickets_labels');

		return $labels;
	}
}
