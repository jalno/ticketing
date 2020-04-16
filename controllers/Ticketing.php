<?php
namespace packages\ticketing\controllers;

use packages\base\{db, view\Error, views\FormError, http, InputValidation, InputValidationException, IO, NotFound, Packages, db\parenthesis, response\file as Responsefile, Translator, Validator};
use packages\Userpanel;
use packages\userpanel\{Date, Log, User};
use packages\ticketing\{Authentication, Authorization, Controller, Department, Events, Logs, Products, Ticket, Ticket_file, Ticket_message, Ticket_param, View, Views};

class Ticketing extends Controller {
	protected $authentication = true;

	private function getTicket(int $ticketID) {
		$unassignedTickets = authorization::is_accessed("unassigned");
		$types = authorization::childrenTypes();
		$me = authentication::getID();
		db::join("userpanel_users as operator", "operator.id=ticketing_tickets.operator_id", "LEFT");
		$ticket = new ticket();
		$ticket->with("client");
		$ticket->with("department");
		$ticket->where("ticketing_tickets.id", $ticketID);
		$parenthesis = new parenthesis();
		if ($types) {
			$parenthesis->orWhere("userpanel_users.type", $types, "IN");
		} else {
			$parenthesis->orWhere("ticketing_tickets.client", $me);
		}
		if ($unassignedTickets) {
			$parenthesis->orWhere("ticketing_tickets.operator_id", null, "IS");
		}
		if ($types) {
			$parenthesis->orWhere("operator.type", $types, "IN");
		} else {
			$parenthesis->orWhere("ticketing_tickets.operator_id", $me);
		}
		$ticket->where($parenthesis);
		$ticket = $ticket->getOne();
		if (!$ticket or ($ticket->client->id != $me and $ticket->department->users and !in_array($me, $ticket->department->users))){
			throw new NotFound;
		}
		if ($ticket->data["operator"]) {
			$ticket->operator = new user($ticket->data["operator"]);
		}
		return $ticket;
	}
	private function getTicketMessage($messageID) {
		$types = authorization::childrenTypes();
		$me = authentication::getID();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets_msgs.user", "LEFT");
		$message = new ticket_message();
		if($types){
			$message->where("userpanel_users.type", $types, "in");
		}else{
			$message->where("userpanel_users.id", $me);
		}
		$message->where("ticketing_tickets_msgs.id", $messageID);
		$message->with("ticket");
		$message = $message->getOne("ticketing_tickets_msgs.*");
		if (!$message or ($message->ticket->client->id != $me and $message->ticket->department->users and !in_array(authentication::getID(), $message->ticket->department->users))){
			throw new NotFound;
		}
		return $message;
	}
	public function index(){
		authorization::haveOrFail('list');
		$view = view::byName(views\ticketlist::class);
		$this->response->setView($view);
		$departments = department::get();
		$view->setDepartment($departments);
		$inputs = $this->checkinputs(array(
			'id' => array(
				'type' => 'number',
				'optional' => true,
			),
			'title' => array(
				'type' => 'string',
				'optional' => true,
			),
			'client' => array(
				'type' => 'number',
				'optional' => true,
			),
			'status' => array(
				'type' => function ($data, $rule, $input) {
					$status = explode(",", $data);
					if (array_diff($status, Ticket::STATUSES)) {
						throw new InputValidationException($input);
					}
					return $status;
				},
				'optional' => true,
				"default" => array(ticket::unread, ticket::read, ticket::answered, ticket::in_progress),
			),
			'priority' => array(
				'type' => 'number',
				'values' => array(ticket::instantaneous, ticket::important, ticket::ordinary),
				'optional' => true,
			),
			'department' => array(
				'type' => 'number',
				'optional' => true,
			),
			'word' => array(
				'type' => 'string',
				'optional' => true,
			),
			'comparison' => array(
				'values' => array('equals', 'startswith', 'contains'),
				'default' => 'contains',
				'optional' => true
			)
		));
		$types = authorization::childrenTypes();
		$unassignedTickets = authorization::is_accessed("unassigned");
		$me = authentication::getID();
		db::join("userpanel_users as operator", "operator.id=ticketing_tickets.operator_id", "LEFT");
		$ticket = new ticket();
		$ticket->with("client");
		$ticket->with("department");
		if ($types) {
			$accessed = array();
			foreach ($departments as $department) {
				if ($department->users) {
					if (in_array($me, $department->users)) {
						$accessed[] = $department->id;
					}
				} else {
					$accessed[] = $department->id;
				}
			}
			if (! empty($accessed)) {
				$ticket->where("ticketing_tickets.department", $accessed, "IN");
			}
		}
		$parenthesis = new parenthesis();
		if ($types) {
			$parenthesis->orWhere("userpanel_users.type", $types, "IN");
		} else {
			$parenthesis->orWhere("ticketing_tickets.client", $me);
		}
		if ($unassignedTickets) {
			$parenthesis->orWhere("ticketing_tickets.operator_id", null, "IS");
		}
		if ($types) {
			$parenthesis->orWhere("operator.type", $types, "IN");
		} else {
			$parenthesis->orWhere("ticketing_tickets.operator_id", $me);
		}
		$ticket->where($parenthesis);
		if (isset($inputs["status"]) and $inputs["status"]) {
			$ticket->where("ticketing_tickets.status", $inputs["status"], "IN");
			$view->setDataForm($inputs["status"], "status");
		}
		foreach(array('id', 'title', 'client', 'title', 'priority', 'department') as $item){
			if(isset($inputs[$item]) and $inputs[$item]){
				$comparison = $inputs['comparison'];
				if(in_array($item, array('id', 'priority', 'department', 'client'))){
					$comparison = 'equals';
				}
				$ticket->where("ticketing_tickets.{$item}", $inputs[$item], $comparison);
			}
		}
		if(isset($inputs['word']) and $inputs['word']){
			$parenthesis = new parenthesis();
			foreach(array('title') as $item){
				if(!isset($inputs[$item]) or !$inputs[$item]){
					$parenthesis->where("ticketing_tickets.{$item}", $inputs['word'], $inputs['comparison'], 'OR');
				}
			}
			db::join("ticketing_tickets_msgs", "ticketing_tickets_msgs.ticket=ticketing_tickets.id", "LEFT");
			db::join("ticketing_files", "ticketing_files.message=ticketing_tickets_msgs.id", "LEFT");
			$parenthesis->orWhere("ticketing_tickets_msgs.text", $inputs['word'], $inputs['comparison']);
			$parenthesis->orWhere("ticketing_files.name", $inputs['word'], $inputs['comparison']);
			$ticket->where($parenthesis);
			$ticket->setQueryOption("DISTINCT");
			$view->setDataForm($inputs['word'], "word");
		}
		$ticket->orderBy('ticketing_tickets.reply_at', 'DESC');
		$ticket->pageLimit = $this->items_per_page;
		$tickets = $ticket->paginate($this->page, array(
			"ticketing_tickets.*",
			"operator.*",
			"userpanel_users.*",
			"ticketing_departments.*",
		));
		foreach ($tickets as $ticket) {
			if ($ticket->data["operator"]) {
				$ticket->operator = new user($ticket->data["operator"]);
			}
		}
		$view->setDataList($tickets);
		$view->setPaginate($this->page, db::totalCount(), $this->items_per_page);
		$this->response->setStatus(true);
		return $this->response;
	}

	public function add() {
		Authorization::haveOrFail('add');
		$view = View::byName(Views\Add::class);
		$this->response->setView($view);
		$view->setProducts(Products::get());
		$view->setDepartmentData((new Department)->where("status", Department::ACTIVE)->get());
		$childrens = Authorization::childrenTypes();
		$inputRules = array();
		if ($childrens) {
			$inputRules['client'] = array(
				'type' => function ($data, $rules, $input) use ($childrens) {
					if ($data and is_numeric($data) and $data != Authentication::getID()) {
						$client = new User();
						$client->where('id', $data);
						$client->where('type', $childrens, 'IN');
						$client = $client->getOne();
						if ($client) {
							return $client;
						}
					}
					return new Validator\NullValue();
				},
			);
		}
		$predefined = $this->checkInputs($inputRules);
		if (isset($predefined['client'])) {
			$view->setClient($predefined['client']);
		}
		$this->response->setStatus(true);
		return $this->response;
	}

	public function store() {
		Authorization::haveOrFail('add');
		$view = View::byName(Views\Add::class);
		$this->response->setView($view);
		$view->setDepartmentData((new Department)->where("status", Department::ACTIVE)->get());
		$view->setProducts(Products::get());
		$children = Authorization::childrenTypes();
		$inputsRules = array(
			'title' => array(
				'type' => 'string',
			),
			'priority' => array(
				'type' => 'number',
				'value' => Ticket::PRIORITIES,
			),
			'department' => array(
				'type' => Department::class,
				'query' => function($query) {
					$query->where("status", Department::ACTIVE);
				},
			),
			'product' => array(
				'type' => 'string',
				'optional' => true,
				'empty' => true
			),
			'service' => array(
				'type' => 'number',
				'optional' => true,
				'empty' => true
			),
			'text' => array(
				'type' => 'string'
			),
			'file' => array(
				'type' => 'file',
				'optional' => true,
				'multiple' => true
			)
		);
		if ($children) {
			$inputsRules['client'] = array(
				'type' => User::class,
				'optional' => true
			);
		}
		$inputs = $this->checkinputs($inputsRules);
		$inputs['department'] = $inputs['department']->id;
		$view->setDataForm($this->inputsvalue($inputsRules));
		$inputs['client'] = isset($inputs['client']) ? $inputs['client'] : Authentication::getUser();
		if (isset($inputs["product"], $inputs["service"])) {
			if ($inputs["product"] and $inputs["service"]) {
				$inputs["product"] = Products::getOne($inputs["product"]);
				if (!$inputs["product"]) {
					throw new InputValidationException("product");
				}
				$inputs["service"] = $inputs["product"]->getServiceById($inputs["client"], $inputs["service"]);
				if (!$inputs["service"]) {
					throw new InputValidationException("service");
				}
			} else {
				unset($inputs["service"]);
				unset($inputs["product"]);
			}
		}
		if (isset($inputs['file'])) {
			if (!is_array($inputs['file'])) {
				throw new InputValidationException("file");
			}
			if (empty($inputs['file'])) {
				unset($inputs['file']);
			} else {
				$files = [];
				foreach ($inputs['file'] as $file) {
					if ($file['error'] == UPLOAD_ERR_OK) {
						$files[] = $file;
					} elseif ($file['error'] != UPLOAD_ERR_NO_FILE) {
						throw new InputValidationException("file");
					}
				}
				$inputs['file'] = [];
				foreach ($files as $file) {
					$name = md5_file($file['tmp_name']);
					$directory = Packages::package('ticketing')->getFilePath('storage/private');
					if (!is_dir($directory)) {
						IO\mkdir($directory);
					}
					if (move_uploaded_file($file['tmp_name'], $directory . '/' . $name)) {
						$inputs['file'][] = [
							'name' => $file['name'],
							'size' => $file['size'],
							'path' => 'private/' . $name,
						];
					} else {
						throw new InputValidationException("file");
					}
				}
			}
		}

		$me = Authentication::getID();
		$ticket = new Ticket();
		$ticket->title	= $inputs['title'];
		$ticket->priority = $inputs['priority'];
		$ticket->client = $inputs['client']->id;
		$ticket->department = $inputs['department'];
		$ticket->status = ($me == $inputs['client']->id ? Ticket::unread : Ticket::answered);
		$ticket->save();
		if (isset($inputs["product"], $inputs["service"])) {
			$ticket->setParam("product", $inputs["product"]->getName());
			$ticket->setParam("service", $inputs["service"]->getId());
		}
		$message = new Ticket_message();
		if (isset($inputs['file'])) {
			foreach ($inputs['file'] as $file) {
				$message->addFile($file);
			}
		}
		$message->ticket = $ticket->id;
		$message->text = $inputs['text'];
		$message->user = $me;
		$message->status = ($me == $inputs["client"]->id ? ticket_message::read : ticket_message::unread);
		$message->save();

		$event = new events\tickets\Add($message);
		$event->trigger();

		$log = new Log();
		$log->user = $me;
		$log->title = t("ticketing.logs.add", ['ticket_id' => $ticket->id]);
		$log->type = logs\tickets\Add::class;
		$log->save();

		$this->response->Go(userpanel\url('ticketing/view/'. $ticket->id));
		$this->response->setStatus(true);
		return $this->response;
	}

	public function view($data) {
		Authorization::haveOrFail('view');
		$view = View::byName(Views\View::class);
		$this->response->setView($view);
		$ticket = $this->getTicket($data['ticket']);
		$view->setTicket($ticket);
		$view->setDepartment(Department::get());
		if (!$ticket->department->isWorking()) {
			$work = $ticket->department->currentWork();
			if ($work->message) {
				$error = new Error;
				$error->setType(Error::NOTICE);
				$error->setCode("ticketing.department.closed");
				$error->setMessage($work->message);
				$view->addError($error);
			}
		}
		if (Authentication::getID() != $ticket->client->id) {
			if ($ticket->status == Ticket::unread) {
				$ticket->status = Ticket::read;
				$ticket->save();
			}
		} else {
			foreach($ticket->message as $row){
				if ($row->status == 0) {
					$row->status = 1;
					$row->save();
				}
			}
		}
		$this->response->setStatus(true);
		return $this->response;
	}

	public function reply($data) {
		$this->response->setStatus(false);
		Authorization::haveOrFail('reply');
		$ticket = $this->getTicket($data['ticket']);
		if ($ticket->param('ticket_lock')) {
			throw new NotFound();
		}
		$inputs = $this->checkinputs(array(
			'text' => array(
				'type' => 'string'
			),
			'file' => array(
				'type' => 'file',
				'optional' => true,
				'multiple' => true,
				'obj' => true,
			)
		));
		if (!$inputs['text'] = strip_tags($inputs['text'])) {
			throw new InputValidationException('text');
		}
		if (isset($inputs['file']) and !$inputs['file']) {
			throw new InputValidationException('file');
		}
		if (!is_array($inputs['file'])) {
			$inputs['file'] = array(
				$inputs['file'],
			);
		}
		$files = array();
		if ($inputs['file']) {
			foreach ($inputs['file'] as $key => $attachment) {
				if (!($attachment instanceof IO\file)) {
					continue;
				}
				$md5 = $attachment->md5();
				$path = "storage/private/" . $md5;
				$uploadedFile = Packages::package('ticketing')->getFile($path);
				$dir = $uploadedFile->getDirectory();
				if (!$dir->exists()) {
					$dir->make(true);
				}
				if (!$attachment->move($uploadedFile)) {
					throw new InputValidationException('file');
				}
				$files[] = array(
					"name" => $attachment->basename,
					"size" => $uploadedFile->size(),
					"path" => "private/" . $md5,
				);
			}
		}
		$ticket_message = new Ticket_message();
		$ticket_message->ticket = $ticket->id;
		$ticket_message->date = Date::time();
		$ticket_message->user = Authentication::getID();
		$ticket_message->text = $inputs['text'];
		$ticket_message->status = ((Authentication::getID() == $ticket->client->id) ? Ticket_message::read : Ticket_message::unread);
		foreach ($files as $file) {
			$ticket_message->addFile($file);
		}
		$ticket_message->save();
		$ticket->status = ((Authorization::childrenTypes() and $ticket->client->id != $ticket_message->user->id) ? Ticket::answered : Ticket::unread);
		$ticket->reply_at = Date::time();
		$ticket->save();

		$event = new events\tickets\Reply($ticket_message);
		$event->trigger();
		$log = new Log();
		$log->user = Authentication::getID();
		$log->title = t("ticketing.logs.reply", array("ticket_id" => $ticket->id));
		$log->type = Logs\tickets\Reply::class;
		$log->save();

		$this->response->Go(userpanel\url("ticketing/view/" . $ticket->id));
		$this->response->setStatus(true);
		return $this->response;
	}
	public function message_delete($data){
		$view = view::byName("\\packages\\ticketing\\views\\message_delete");
		authorization::haveOrFail('message_delete');

		$ticket_message = $this->getTicketMessage($data['ticket']);
		$view->setMessageData($ticket_message);
		if(http::is_post()){
			$ticket = $ticket_message->ticket;
			$ticket_message->delete();
			$this->response->setStatus(true);
			$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function message_edit($data){
		$view = view::byName("\\packages\\ticketing\\views\\message_edit");
		authorization::haveOrFail('message_edit');
		$ticket_message = $this->getTicketMessage($data['ticket']);
		$view->setMessageData($ticket_message);
		if(http::is_post()){
			$this->response->setStatus(false);
			$inputsRules = array(
				'text' => array(
					'type' => 'string',
				)
			);
			try {
				$inputs = $this->checkinputs($inputsRules);
				$parameters = ['oldData' => ['message' => $ticket_message]];
				$ticket_message->text = $inputs['text'];
				$ticket_message->save();

				$log = new log();
				$log->user = authentication::getID();
				$log->title = translator::trans("ticketing.logs.edit", ['ticket_id' => $ticket_message->ticket->id]);
				$log->type = logs\tickets\edit::class;
				$log->parameters = $parameters;
				$log->save();
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('ticketing/view/'.$ticket_message->ticket->id));
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function edit($data) {
		$view = View::byName(Views\Edit::class);
		Authorization::haveOrFail('edit');
		$ticket = $this->getTicket($data['ticket']);
		$view->setDepartment(Department::get());
		$view->setTicket($ticket);
		$this->response->setView($view);		
		$inputs = $this->checkinputs(array(
			'close' => array(
				'type' => 'string',
				'optional' => true,
			)));
		if(isset($inputs['close'])){
			if(strtolower($inputs['close']) == 'yes'){
				$view->setDataForm(ticket::closed, 'status');
			}
		}
		$this->response->setStatus(true);
		return $this->response;
	}

	public function update($data) {
		$view = View::byName(Views\Edit::class);
		Authorization::haveOrFail('edit');
		$ticket = $this->getTicket($data['ticket']);
		$view->setDepartment(Department::get());
		$view->setTicket($ticket);
		$this->response->setView($view);
		$users = $ticket->department->users;
		$inputs = $this->checkinputs(array(
			'title' => array(
				'type' => 'string',
				'optional' => true
			),
			'priority' => array(
				'type' => 'number',
				'values' => Ticket::PRIORITIES,
				'optional' => true
			),
			'department' => array(
				'type' => Department::class,
				'optional' => true
			),
			'client' => array(
				'type' => User::class,
				'optional' => true
			),
			'status' => array(
				'type' => 'number',
				'values' => Ticket::STATUSES,
				'optional' => true
			),
			"operator" => array(
				"type" => User::class,
				"optional" => true,
				"query" => function($query) {
					$priority = db::subQuery();
					$priority->setQueryOption("DISTINCT");
					$priority->get("userpanel_usertypes_priorities", null, "parent");
					$permission = db::subQuery();
					$permission->where("name", "ticketing_view");
					$permission->get("userpanel_usertypes_permissions", null, "type");
					$query->where("type", $priority, "IN");
					$query->where("type", $permission, "IN");
				}
			),
		));
		if (isset($inputs["operator"]) and $users) {
			if (! in_array($inputs["operator"]->id, $users)) {
				throw new InputValidationException("operator");
			}
		}
		$parameters = array('oldData' => array());
		if (isset($inputs['status'])) {
			$inputs['oldStatus'] = $ticket->status;
		}
		foreach (array('title', 'priority', 'status') as $item) {
			if (isset($inputs[$item])){
				if ($inputs[$item] != $ticket->$item){
					$parameters['oldData'][$item] = $ticket->$item;
					$ticket->$item = $inputs[$item];
				}
			}
		}
		foreach (array('department', 'client') as $item){
			if (isset($inputs[$item])) {
				if ($inputs[$item]->id != $ticket->$item->id) {
					$parameters['oldData'][$item] = $ticket->$item;
					$ticket->$item = $inputs[$item]->id;
				}
			}
		}
		if (isset($inputs['operator'])) {
			if ($inputs['operator']->id !== $ticket->operator_id) {
				$parameters['oldData']['operator'] = $ticket->operator_id;
				$ticket->operator_id = $inputs['operator']->id;
			}
		}
		$ticket->save();
		if (isset($inputs['oldStatus'])) {
			if ($inputs['oldStatus'] != $ticket->status) {
				if ($ticket->status == Ticket::closed) {
					$event = new events\tickets\Close($ticket);
					$event->trigger();
				} elseif ($ticket->status == Ticket::in_progress) {
					$event = new events\tickets\Inprogress($ticket);
					$event->trigger();
				}
			}
		}

		$log = new Log();
		$log->user = Authentication::getID();
		$log->title = t("ticketing.logs.edit", array('ticket_id' => $ticket->id));
		$log->type = logs\tickets\Edit::class;
		$log->parameters = $parameters;
		$log->save();

		$this->response->setStatus(true);
		$this->response->Go(userpanel\url('ticketing'));
		return $this->response;
	}

	public function lock($data){
		$view = view::byName("\\packages\\ticketing\\views\\lock");
		authorization::haveOrFail('lock');

		$ticket = $this->getTicket($data['ticket']);
		$view->setTicketData($ticket);
		$this->response->setStatus(false);
		if(http::is_post()){
			if($ticket->setParam('ticket_lock', 1)){
				$log = new log();
				$log->user = authentication::getID();
				$log->title = translator::trans("ticketing.logs.lock", ['ticket_id' => $ticket->id]);
				$log->type = logs\tickets\lock::class;
				$log->save();

				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id));
			}
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function unlock($data){
		$view = view::byName("\\packages\\ticketing\\views\\unlock");
		authorization::haveOrFail('unlock');

		$ticket = $this->getTicket($data['ticket']);
		$view->setTicketData($ticket);
		$this->response->setStatus(false);
		if(http::is_post()){
			$param = ticket_param::where('ticket', $ticket->id)->where('name', 'ticket_lock')->getOne();
			$param->delete();

			$log = new log();
			$log->user = authentication::getID();
			$log->title = translator::trans("ticketing.logs.unlock", ['ticket_id' => $ticket->id]);
			$log->type = logs\tickets\unlock::class;
			$log->save();

			$this->response->setStatus(true);
			$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function delete($data){
		$view = view::byName("\\packages\\ticketing\\views\\delete");
		authorization::haveOrFail('delete');
		$ticket = $this->getTicket($data['ticket']);
		$view->setTicketData($ticket);
		$this->response->setStatus(false);
		if(http::is_post()){
			$log = new log();
			$log->user = authentication::getID();
			$log->title = translator::trans("ticketing.logs.delete", ['ticket_id' => $ticket->id]);
			$log->type = logs\tickets\delete::class;
			$log->parameters = ['ticket' => $ticket];
			$log->save();

			$ticket->delete();
			$this->response->setStatus(true);
			$this->response->Go(userpanel\url('ticketing'));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function download($data){
		Authorization::haveOrFail('files-download');
		$types = authorization::childrenTypes();
		db::join("ticketing_tickets_msgs", "ticketing_tickets_msgs.id=ticketing_files.message", 'INNER');
		db::join("ticketing_tickets", "ticketing_tickets.id=ticketing_tickets_msgs.ticket", "INNER");
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "INNER");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::where("ticketing_files.id", $data['file']);
		if($fileData = db::getOne("ticketing_files", array("ticketing_files.*"))){
			$file = new ticket_file($fileData);
			$responsefile = new responsefile();
			$responsefile->setLocation(packages::package('ticketing')->getFilePath('storage/'.$file->path));
			$responsefile->setSize($file->size);
			$responsefile->setName($file->name);
			$this->response->setFile($responsefile);
			return $this->response;
		}else{
			throw new NotFound;
		}
	}
	public function getServices(){
		$this->response->setStatus(false);
		try{
			$inputs = $this->checkinputs(array(
				'product' => array(
					'type' => 'string'
				),
				'client' => array(
					'type' => 'number',
					'optional' => true,
					'default' => authentication::getID()
				)
			));
			products::get();
			if(!products::has($inputs['product'])){
				throw new inputValidation("product");
			}
			$inputs['client'] = user::byId($inputs['client']);
			if(!$inputs['client']){
				throw new inputValidation("client");
			}
			$product = products::getOne($inputs['product']);
			$services = array();
			foreach($product->getServices($inputs['client']) as $service){
				$services[] = array(
					'id' => $service->getId(),
					'title' => $service->getTitle()
				);
			}
			$this->response->setdata($services, "items");
			$this->response->setStatus(true);
		}catch(inputValidation $error){
			$this->response->addError(FormError::fromException($error));
		}
		return $this->response;
	}
	public function confirmClose(array $data){
		authorization::haveOrFail('close');
		$ticket = $this->getTicket($data['ticket']);
		if($ticket->status == ticket::closed or $ticket->param('ticket_lock')){
			throw new NotFound();
		}
		$view = view::byName("\\packages\\ticketing\\views\\close");
		$view->setTicket($ticket);
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function close(array $data){
		authorization::haveOrFail('close');
		$ticket = $this->getTicket($data['ticket']);
		if($ticket->status == ticket::closed or $ticket->param('ticket_lock')){
			throw new NotFound();
		}
		$view = view::byName("\\packages\\ticketing\\views\\close");
		$view->setTicket($ticket);
		$this->response->setStatus(false);
		$parameters = ['oldData' => ['status' => $ticket->status]];
		$ticket->status = ticket::closed;
		$ticket->save();
		$event = new events\tickets\close($ticket);
		$event->trigger();

		$log = new log();
		$log->user = authentication::getID();
		$log->title = translator::trans("ticketing.logs.edit", ['ticket_id' => $ticket->id]);
		$log->type = logs\tickets\edit::class;
		$log->parameters = $parameters;
		$log->save();

		$this->response->setStatus(true);
		$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id));
		$this->response->setView($view);
		return $this->response;
	}
	public function confirmInProgress(array $data){
		authorization::haveOrFail('edit');
		$ticket = $this->getTicket($data['ticket']);
		if($ticket->status == ticket::in_progress){
			throw new NotFound();
		}
		$view = view::byName("\\packages\\ticketing\\views\\inprogress");
		$view->setTicket($ticket);
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function inProgress(array $data){
		authorization::haveOrFail('edit');
		$ticket = $this->getTicket($data['ticket']);
		if($ticket->status == ticket::in_progress){
			throw new NotFound();
		}
		$view = view::byName("\\packages\\ticketing\\views\\inprogress");
		$view->setTicket($ticket);
		$this->response->setStatus(false);
		$parameters = ['oldData' => ['status' => $ticket->status]];
		$ticket->status = ticket::in_progress;
		$ticket->save();
		$event = new events\tickets\inprogress($ticket);
		$event->trigger();

		$log = new log();
		$log->user = authentication::getID();
		$log->title = translator::trans("ticketing.logs.edit", ['ticket_id' => $ticket->id]);
		$log->type = logs\tickets\edit::class;
		$log->parameters = $parameters;
		$log->save();

		$this->response->setStatus(true);
		$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id));
		$this->response->setView($view);
		return $this->response;
	}
	public function department($data) {
		authorization::haveOrFail('add');
		$department = department::byId($data['department']);
		if (!$department) {
			throw new NotFound();
		}
		$this->response->setStatus(true);
		$data = $department->toArray();
		if ($currentWork = $department->currentWork()) {
			$data["currentWork"] =  $currentWork->toArray();
		}
		$this->response->setData($data, "department");
		return $this->response;
	}
}
