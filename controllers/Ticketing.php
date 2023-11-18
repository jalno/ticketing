<?php
namespace packages\ticketing\controllers;

use packages\base\{db, db\DuplicateRecord, view\Error, views\FormError, http, InputValidation, InputValidationException, IO, NotFound, Options, Packages, db\parenthesis, Response, response\file as Responsefile, Translator, Validator};
use packages\ticketing\{Authentication, Authorization, Controller, Department, Events, Logs, Products, Ticket, Ticket_file, Ticket_message, Ticket_param, View, Views};
use packages\ticketing\Template;
use packages\ticketing\Parsedown;
use packages\ticketing\Label;
use packages\Userpanel;
use packages\userpanel\{Date, Log, User};
use packages\userpanel\AuthorizationException;

class Ticketing extends Controller {

	protected $authentication = true;

	private static $types = [];
	private static $currentUserID;
	private static $departments = [];
	private static $hasAccessTounassignedTickets = false;

	private static function prepareCheckAccess() {

		self::$types = Authorization::childrenTypes();

		self::$currentUserID = Authentication::getID();

		self::$departments = Department::get();

		self::$hasAccessTounassignedTickets = Authorization::is_accessed("unassigned");
	}

	private static function checkAccesses(DB\DBObject $model) {

		DB::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "INNER");
		DB::join("userpanel_users as operator", "operator.id=ticketing_tickets.operator_id", "LEFT");

		if (self::$types) {
			$accessed = array();
			foreach (self::$departments as $department) {
				if ($department->users) {
					if (in_array(self::$currentUserID, $department->users)) {
						$accessed[] = $department->id;
					}
				} else {
					$accessed[] = $department->id;
				}
			}
			if (!empty($accessed)) {
				$model->where("ticketing_tickets.department", $accessed, "IN");
			}
		}

		$parenthesis = new Parenthesis();

		$parenthesis->where("ticketing_tickets.client", self::$currentUserID);

		if (self::$hasAccessTounassignedTickets and self::$types) {
			$parenthesis->where("ticketing_tickets.operator_id", null, "IS");
		} else {
			$parenthesis->orWhere("ticketing_tickets.operator_id", self::$currentUserID);
		}

		if (self::$types) {
			$parenthesis->orWhere("userpanel_users.type", self::$types, "IN");
		}

		$model->where($parenthesis);
	}

	private static function checkAccessToTickets(): Ticket {

		self::prepareCheckAccess();

		$model = new Ticket();

		$model->with("department");

		self::checkAccesses($model);

		return $model;
	}

	private function getTicket(int $ticketID) {

		$ticket = self::checkAccessToTickets();

		$ticket->where("ticketing_tickets.id", $ticketID);
		$ticket = $ticket->getOne();
		if (!$ticket){
			throw new NotFound;
		}
		if (isset($ticket->data["operator"]) and $ticket->data["operator"]) {
			$ticket->operator = new user($ticket->data["operator"]);
		}
		return $ticket;
	}
	private function getTicketMessage($messageID) {

		self::prepareCheckAccess();

		DB::join("ticketing_tickets", "ticketing_tickets.id=ticketing_tickets_msgs.ticket", "INNER");
		DB::join("userpanel_users as message_sender", "message_sender.id=ticketing_tickets_msgs.user", "INNER");

		$message = new ticket_message();

		self::checkAccesses($message);

		$message->where("ticketing_tickets_msgs.id", $messageID);

		if (self::$types) {
			$message->where("message_sender.type", self::$types, "IN");
		} else {
			$message->where("ticketing_tickets_msgs.user", self::$currentUserID);
		}

		$message = $message->getOne("ticketing_tickets_msgs.*");
		if (!$message){
			throw new NotFound;
		}
		return $message;
	}

	public function index(?array $data = []): Response
	{
		Authorization::haveOrFail('list');

		$user = null;
		$view = null;
		if (isset($data['id'])) {
			if (!is_numeric($data['id']) or $data['id'] == Authentication::getID()) {
				throw new NotFound();
			}

			$types = Authorization::childrenTypes();
			if (!$types) {
				throw new NotFound();
			}

			$query = new User();
			$query->where('type', $types, 'in');
			$user = $query->byId($data['id']);
			if (!$user) {
				throw new NotFound();
			}

			$view = View::byName(\themes\clipone\views\users\View::class);

			$view->setData($user, 'user');
			$view->isTab(true);
			$view->triggerTabs();
			$view->activeTab('ticket');
		} else {
			$view = View::byName(views\ticketlist::class);
		}

		$departments = (new Department)->where('status', Department::ACTIVE)->get();
		$view->setDepartment($departments);

		$me = Authentication::getID();
		$types = Authorization::childrenTypes();
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
			'operator' => array(
				'type' => User::class,
				'optional' => true,
				'query' => function($query) use ($types, $me) {
					if ($types) {
						$query->where("type", $types, "IN");
					} else {
						$query->where("id", $me->id);
					}
				},
				'fileds' => array('id'),
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
			),
			'unread' => array(
				'type' => 'bool',
				'optional' => true,
				'default' => false,
			),
			'message_sender' => array(
				'type' => User::class,
				'optional' => true,
				'query' => function($query) use ($types, $me) {
					if ($types) {
						$query->where("type", $types, "IN");
					} else {
						$query->where("id", $me->id);
					}
				},
				'fileds' => array('id'),
			),
			'labels' => [
				'type' => 'array',
				'explode' => ',',
				'duplicate' => 'remove',
				'each' => [
					'type' => 'int',
				],
				'optional' => true,
			],
		));

		$ticket = self::checkAccessToTickets();

		if ($user) {
			$inputs["client"] = $user->id;
		}

		if ($inputs["unread"]) {
			$inputs["status"] = Ticket::STATUSES;
		}
		if (isset($inputs["status"]) and $inputs["status"]) {
			$ticket->where("ticketing_tickets.status", $inputs["status"], "IN");
			$view->setDataForm($inputs["status"], "status");
		}
		foreach (array('id', 'title', 'client', 'operator', 'title', 'priority', 'department') as $item) {
			if (!isset($inputs[$item]) or !$inputs[$item]) {
				continue;
			}
			$value = $inputs[$item];
			$key = "ticketing_tickets.{$item}";
			$comparison = $inputs['comparison'];
			if (in_array($item, array('id', 'priority', 'department', 'client', 'operator'))) {
				$comparison = 'equals';
			}
			if ($item == 'operator') {
				$key .= '_id';
				$value = $value->id;
			}
			$ticket->where($key, $value, $comparison);
		}
		if (isset($inputs['word']) or isset($inputs['message_sender']) or $inputs["unread"]) {
			db::join("ticketing_tickets_msgs", "ticketing_tickets_msgs.ticket=ticketing_tickets.id", "LEFT");
		}
		if (isset($inputs['message_sender'])) {
			$ticket->where("ticketing_tickets_msgs.user", $inputs['message_sender']->id);
			$ticket->where("ticketing_tickets.client", $inputs['message_sender']->id, "!=");
		}
		if ($inputs["unread"]) {
			$ticket->where("ticketing_tickets_msgs.status", Ticket_message::unread);
		}
		if(isset($inputs['word'])){
			$parenthesis = new parenthesis();
			foreach(array('title') as $item){
				if(!isset($inputs[$item]) or !$inputs[$item]){
					$parenthesis->where("ticketing_tickets.{$item}", $inputs['word'], $inputs['comparison'], 'OR');
				}
			}
			db::join("ticketing_files", "ticketing_files.message=ticketing_tickets_msgs.id", "LEFT");
			$parenthesis->orWhere("ticketing_tickets_msgs.text", $inputs['word'], $inputs['comparison']);
			$parenthesis->orWhere("ticketing_files.name", $inputs['word'], $inputs['comparison']);
			$ticket->where($parenthesis);
			$ticket->setQueryOption("DISTINCT");
			$view->setDataForm($inputs['word'], "word");
		}

		if (isset($inputs['labels'])) {
			DB::join('ticketing_tickets_labels', 'ticketing_tickets_labels.ticket_id=ticketing_tickets.id', 'inner');

			$ticket->where('ticketing_tickets_labels.label_id', $inputs['labels'], 'in');
			$ticket->setQueryOption("DISTINCT");
		}
		$ticket->orderBy('ticketing_tickets.reply_at', 'DESC');
		$ticket->pageLimit = $this->items_per_page;
		$tickets = $ticket->paginate($this->page, array(
			"ticketing_tickets.*",
			"operator.*",
			"userpanel_users.*",
			"ticketing_departments.*",
		));
		$view->setPaginate($this->page, db::totalCount(), $this->items_per_page);

		$canViewLabels = (
			Authorization::is_accessed('view_labels') or
			Authorization::is_accessed('edit')
		);

		$labelIds = [];
		foreach ($tickets as $ticket) {
			if ($ticket->data["operator"]) {
				$ticket->operator = new user($ticket->data["operator"]);
			}

			$ticket->client = new User($ticket->data['userpanel_users']);
			unset($ticket->data['userpanel_users']);

			if ($canViewLabels) {
				$query = DB::where('ticketing_tickets_labels.ticket_id', $ticket->id);
				$labels = array_column($query->get('ticketing_tickets_labels', null, 'label_id'), 'label_id');

				$ticket->labels = $labels;
				$labelIds = array_unique(array_merge($labelIds, $labels));
			}
		}
		$view->setDataList($tickets);

		if ($labelIds and $canViewLabels) {
			$query = new Label();
			$query->where('id', $labelIds, 'in');
			$query->where('status', Label::ACTIVE);
			$view->labels = $query->get();
		}

		$view->canViewLabels = $canViewLabels;
		$this->response->setView($view);
		$this->response->setStatus(true);
		return $this->response;
	}

	public function add(): Response {
		Authorization::haveOrFail('add');
		$view = View::byName(Views\Add::class);
		$this->response->setView($view);
		$view->setProducts(Products::get());
		$view->setDepartmentData((new Department)->where("status", Department::ACTIVE)->get());
		$children = Authorization::childrenTypes();
		$rules = array();
		if ($children) {
			$canAddMultiuser = Authorization::is_accessed('add_multiuser');
			$rules = array(
				'client' => array(
					'type' => function ($data, $rules, $input) use ($children, $canAddMultiuser) {
						if ($data and is_string($data)) {
							$clientsIDs = array_unique(array_filter(explode(',', trim($data)), function ($id) {
								return ($id and is_numeric($id) and $id > 0);
							}));
							if (!$clientsIDs) {
								return new Validator\NullValue();
							}
							$clients = (new User())
										->where('id', $clientsIDs, 'IN')
										->where('type', $children, 'IN')
										->get(null, ['id', 'name', 'lastname', 'email', 'cellphone']);
							return $clients;
						}
						return new Validator\NullValue();
					},
					'optional' => true,
				)
			);
			if ($canAddMultiuser) {
				$rules['multiuser_mode'] = array(
					'type' => 'bool',
					'default' => false,
					'optional' => true,
				);
			}
			$predefined = $this->checkInputs($rules);
			if ($canAddMultiuser) {
				$view->selectMultiUser($predefined['multiuser_mode']);
			}
			if (isset($predefined['client'])) {
				$view->setClients($predefined['client']);
			}
		}
		$this->response->setStatus(true);
		return $this->response;
	}

	public function store(): Response {
		Authorization::haveOrFail('add');
		$view = View::byName(Views\Add::class);
		$this->response->setView($view);
		$view->setProducts(Products::get());
		$view->setDepartmentData((new Department)->where("status", Department::ACTIVE)->get());

		$currentUser = Authentication::getUser();
		$children = Authorization::childrenTypes();
		$canAddMultiuser = Authorization::is_accessed('add_multiuser');
		$hasAccessToEnableDisableNotification = Authorization::is_accessed("enable_disabled_notification");
		$defaultBehavior = Ticket::sendNotificationOnSendTicket($hasAccessToEnableDisableNotification ? $currentUser : null);
		$canUseTemplate = Authorization::is_accessed('use_templates');
		
		$rules = array(
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
			),
			'service' => array(
				'type' => 'number',
				'optional' => true,
			),
			'content' => array(
				'type' => 'string',
				'multiLine' => true,
			),
			'file' => array(
				'type' => 'file',
				'obj' => true,
				'multiple' => true,
				'optional' => true,
			),
			'send_notification' => array(
				'type' => 'bool',
				'optional' => true,
				'default' => $defaultBehavior,
			),
		);

		if ($canUseTemplate) {
			$rules['message_format'] = [
				'type' => 'string',
				'values' => [Ticket_message::html, Ticket_message::markdown],
				'optional' => true,
			];
		}

		if ($children) {
			$rules['client'] = array(
				'type' => function ($data, $rule, $input) use ($children, $canAddMultiuser) {
					if (!$data) {
						throw new InputValidationException($input);
					}
					if (!is_array($data)) {
						$data = [$data];
					}
					if (!$canAddMultiuser and count($data) > 1) {
						throw new InputValidationException($input);
					}
					$items = array();
					foreach ($data as $key => $userID) {
						if (!is_numeric($userID) or $userID <= 0) {
							throw new InputValidationException("{$input}[{$key}]");
						}
						if (in_array($userID, $items)) {
							throw new DuplicateRecord("{$input}[{$key}]");
						}
						$items[] = $userID;
					}
					unset($items);
					$clients = (new User())
								->where('type', $children, 'IN')
								->where('id', $data, 'IN')
								->get();
					if (count($clients) != count($data)) {
						$ids = array_diff($data, array_column($clients, 'id'));
						reset($ids);
						$key = key($ids);
						throw new InputValidationException("{$input}[{$key}]");
					}
					return $clients;
				},
				'optional' => true,
			);
		}
		if (!$hasAccessToEnableDisableNotification) {
			unset($rules["send_notification"]);
		}
		$view->setDataForm($this->inputsvalue($rules));
		$inputs = $this->checkinputs($rules);
		if (!$hasAccessToEnableDisableNotification) {
			$inputs["send_notification"] = $defaultBehavior;
		}
		if (isset($inputs['file']) and empty($inputs['file'])) {
			unset($inputs['file']);
		}
		if (!isset($inputs['client'])) {
			$inputs['client'] = [$currentUser];
		}
		if (count($inputs['client']) == 1) {
			if (isset($inputs['product'])) {
				$allowedProducts = $inputs['department']->getProducts();
				// if $allowedProducts is empty, all Products is acceptable for this department
				if ($allowedProducts and !in_array($inputs['product'], $allowedProducts)) {
					throw new InputValidationException('product');
				}
				$inputs['product'] = Products::getOne($inputs['product']);
				if (!$inputs['product']) {
					throw new InputValidationException('product');
				}
				if (!isset($inputs['service'])) {
					throw new InputValidationException('service');
				}
				$inputs['service'] = $inputs['product']->getServiceById($inputs['client'][0], $inputs['service']);
				if (!$inputs['service']) {
					throw new InputValidationException('service');
				}
			} else if ($inputs['department']->isMandatoryChooseProduct() and !Authorization::is_accessed('add_override-force-product-choose')) {
				throw new InputValidationException('product');
			}
		} else {
			unset($inputs['service'], $inputs['product']);
		}

		if (isset($inputs['file'])) {
			$attachments = $inputs['file'];
			$inputs['file'] = [];
			foreach ($attachments as $attachment) {
				$md5 = $attachment->md5();
				$file = Packages::package("ticketing")->getFile("storage/private/{$md5}");
				$directory = $file->getDirectory();
				if (!$directory->exists()) {
					$directory->make(true);
				}
				if (!$attachment->copyTo($file)) {
					throw new InputValidationException("file");
				}
				$inputs['file'][] = [
					'name' => $attachment->basename,
					'size' => $file->size(),
					'path' => "private/{$md5}",
				];
			}
		}

		if ($hasAccessToEnableDisableNotification and $defaultBehavior != $inputs["send_notification"]) {
			$currentUser->setOption(Ticket::SEND_NOTIFICATION_USER_OPTION_NAME, $inputs["send_notification"]);
		}
		$hasAccessedToUnassigned = Authorization::is_accessed("unassigned");

		foreach ($inputs['client'] as $client) {
			$ticket = new Ticket();
			$ticket->title	= $inputs['title'];
			$ticket->priority = $inputs['priority'];
			$ticket->client = $client->id;
			$ticket->department = $inputs['department']->id;
			$ticket->status = ($currentUser->id == $client->id ? Ticket::unread : Ticket::answered);
			if ($currentUser->id != $client->id and !$hasAccessedToUnassigned) {
				$ticket->operator_id = $currentUser->id;
			}
			$ticket->save();

			if (isset($inputs["product"], $inputs["service"])) {
				$ticket->setParam("product", $inputs["product"]->getName());
				$ticket->setParam("service", $inputs["service"]->getId());
			}

			$content = $inputs['content'];
			if ($canUseTemplate) {
				$content = str_replace(
					Template::PREDEFINED_VARIABLES,
					[$client->name, $client->lastname, $client->getFullName(), $client->email, $client->getCellphoneWithDialingCode()],
					$content,
				);
			}

			$message = new Ticket_message();
			if (isset($inputs['file'])) {
				foreach ($inputs['file'] as $file) {
					$message->addFile($file);
				}
			}
			$message->ticket = $ticket->id;
			$message->text = $content;
			$message->user = $currentUser->id;
			$message->status = ($currentUser->id == $client->id ? Ticket_message::read : Ticket_message::unread);
			
			if (isset($inputs['message_format'])) {
				$message->format = $inputs['message_format'];
			}
			
			$message->save();

			$log = new Log();
			$log->user = $currentUser->id;
			$log->title = t("ticketing.logs.add", ['ticket_id' => $ticket->id]);
			$log->type = logs\tickets\Add::class;
			$log->save();

			if ($inputs["send_notification"]) {
				(new events\tickets\Add($message))->trigger();
			}
		}

		$this->response->Go(userpanel\url(count($inputs['client']) == 1 ? "ticketing/view/{$ticket->id}" : "ticketing"));
		$this->response->setStatus(true);
		return $this->response;
	}

	public function view($data)
	{
		Authorization::haveOrFail('view');
		$view = View::byName(Views\View::class);
		$this->response->setView($view);

		$ticket = $this->getTicket($data['ticket']);
		$canViewLabels = (
			Authorization::is_accessed('view_labels') or
			Authorization::is_accessed('edit')
		);
		if ($canViewLabels) {
			$ticket->labels = $ticket->getLabels();
		}
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);

		$view->canViewLabels = $canViewLabels;
		$view->setTicket($ticket);
		$departments = (new Department)->where('status', Department::ACTIVE)->get();
		$view->setDepartment($departments);

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
		if ($ticket->param('ticket_lock') or $ticket->department->status == Department::DEACTIVE) {
			throw new NotFound();
		}
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);
		$hasAccessToEnableDisableNotification = Authorization::is_accessed("enable_disabled_notification");
		$currentUser = Authentication::getUser();
		$defaultBehavior = Ticket::sendNotificationOnSendTicket($hasAccessToEnableDisableNotification ? $currentUser : null);
		$canUseTemplate = Authorization::is_accessed('use_templates');

		$inputsRules = array(
			'content' => array(
				'type' => 'string',
				'multiLine' => true,
			),
			'file' => array(
				'type' => 'file',
				'optional' => true,
				'multiple' => true,
				'obj' => true,
			),
			"send_notification" => array(
				'type' => 'bool',
				'optional' => true,
				'default' => $defaultBehavior,
			),
		);

		if ($canUseTemplate) {
			$inputsRules['message_format'] = [
				'type' => 'string',
				'values' => [Ticket_message::html, Ticket_message::markdown],
				'optional' => true,
			];
		}

		if (!$hasAccessToEnableDisableNotification) {
			unset($inputsRules["send_notification"]);
		}
		$inputs = $this->checkinputs($inputsRules);
		if (!$hasAccessToEnableDisableNotification) {
			$inputs["send_notification"] = $defaultBehavior;
		}

		if (isset($inputs['file']) and !$inputs['file']) {
			throw new InputValidationException('file');
		}
		$files = array();
		if (isset($inputs['file']) and $inputs['file']) {
			if (!is_array($inputs['file'])) {
				$inputs['file'] = array($inputs['file']);
			}
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

		$content = $inputs['content'];
		if ($canUseTemplate) {
			$content = str_replace(
				Template::PREDEFINED_VARIABLES,
				[$ticket->client->name, $ticket->client->lastname, $ticket->client->getFullName(), $ticket->client->email, $ticket->client->getCellphoneWithDialingCode()],
				$content,
			);
		}

		$ticket_message = new Ticket_message();
		$ticket_message->ticket = $ticket->id;
		$ticket_message->date = Date::time();
		$ticket_message->user = Authentication::getID();
		$ticket_message->text = $content;
		$ticket_message->status = ((Authentication::getID() == $ticket->client->id) ? Ticket_message::read : Ticket_message::unread);
		foreach ($files as $file) {
			$ticket_message->addFile($file);
		}
		
		if (isset($inputs['message_format'])) {
			$ticket_message->format = $inputs['message_format'];
		}

		$ticket_message->save();

		$ticket->status = ((Authorization::childrenTypes() and $ticket->client->id != $ticket_message->user->id) ? Ticket::answered : Ticket::unread);
		$ticket->reply_at = Date::time();
		$ticket->save();

		$log = new Log();
		$log->user = Authentication::getID();
		$log->title = t("ticketing.logs.reply", array("ticket_id" => $ticket->id));
		$log->type = Logs\tickets\Reply::class;
		$log->save();


		
		if ($hasAccessToEnableDisableNotification and $inputs["send_notification"] != $defaultBehavior) {
			$currentUser->setOption(Ticket::SEND_NOTIFICATION_USER_OPTION_NAME, $inputs["send_notification"]);
		}
		if ($inputs["send_notification"]) {
			$event = new events\tickets\Reply($ticket_message);
			$event->trigger();
		}

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
				'content' => array(
					'type' => 'string',
					'multiLine' => true,
				),
			);
			try {
				$inputs = $this->checkinputs($inputsRules);

				$content = $inputs['content'];
				if (Authorization::is_accessed('use_templates')) {
					$ticket = $ticket_message->ticket;
					$content = str_replace(
						Template::PREDEFINED_VARIABLES,
						[$ticket->client->name, $ticket->client->lastname, $ticket->client->getFullName(), $ticket->client->email, $ticket->client->getCellphoneWithDialingCode()],
						$content,
					);
				}

				$parameters = ['oldData' => ['message' => $ticket_message]];
				$ticket_message->text = $content;
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
		$canViewLabels = (
			Authorization::is_accessed('view_labels') or
			Authorization::is_accessed('edit')
		);
		if ($canViewLabels) {
			$ticket->labels = $ticket->getLabels();
		}
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);
		$departments = (new Department)->where('status', Department::ACTIVE)->get();
		$view->setDepartment($departments);
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
		$canViewLabels = (
			Authorization::is_accessed('view_labels') or
			Authorization::is_accessed('edit')
		);
		if ($canViewLabels) {
			$ticket->labels = $ticket->getLabels();
		}
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);
		$departments = (new Department)->where('status', Department::ACTIVE)->get();
		$view->setDepartment($departments);
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
			'labels' => [
				'type' => 'array',
				'explode' => ',',
				'each' => [
					'type' => Label::class,
					'query' => function (Label $query) {
						$query->where('status', Label::ACTIVE);
					},
				],
				'optional' => true,
				'empty' => true,
			],
			'delete-labels' => [
				'type' => 'array',
				'explode' => ',',
				'each' => [
					'type' => Label::class,
				],
				'optional' => true,
			],
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

		if (array_key_exists('labels', $inputs)) {
			$oldLabels = $ticket->getLabels();
			$newLabels = $ticket->setLabels($inputs['labels'] ? array_column($inputs['labels'], 'id') : []);

			$old = array_column($oldLabels, 'id');
			$new = array_column($newLabels, 'id');

			$deleted = array_diff($old, $new);
			if ($deleted) {
				$parameters['oldData']['labels'] = array_map(
					fn (Label $label) => [
						'id' => $label->getID(),
						'title' => $label->getTitle(),
						'color' => $label->getColor(),
					],
					array_filter($oldLabels, fn (Label $label) => in_array($label->id, $deleted))
				);
			}
			$added = array_diff($new, $old);
			if ($added) {
				$parameters['newData'] = [
					'labels' => array_map(
						fn (Label $label) => [
							'id' => $label->getID(),
							'title' => $label->getTitle(),
							'color' => $label->getColor(),
						],
						array_filter($newLabels, fn (Label $label) => in_array($label->id, $added))
					),
				];
			}
		} elseif (isset($inputs['delete-labels'])) {
			$deletedLabels = $ticket->deleteLabels(array_column($inputs['delete-labels'], 'id'));
			if ($deletedLabels) {
				$parameters['oldData']['labels'] = array_map(
					fn (Label $label) => [
						'id' => $label->getID(),
						'title' => $label->getTitle(),
						'color' => $label->getColor(),
					],
					$deletedLabels
				);
			}
		}

		if ($canViewLabels) {
			$ticket->labels = $ticket->getLabels();
		}

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
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);
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
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);
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
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);
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
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);
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
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);
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
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);
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
		$ticket->client = new User($ticket->data['userpanel_users']);
		unset($ticket->data['userpanel_users']);
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

	public function previewMessage(): Response
	{
		if (
			!Authorization::is_accessed('reply') and
			!Authorization::is_accessed('add') and
			!Authorization::is_accessed('settings_templates_add') and
			!Authorization::is_accessed('settings_templates_edit')
		) {
			throw new AuthorizationException('ticketing_editor_preview');
		}

		$inputs = $this->checkInputs([
			'format' => [
				'type' => 'string',
				'values' => [Ticket_message::html, Ticket_message::markdown],
			],
			'content' => [
				'type' => 'string',
				'multiLine' => true,
			],
		]);

		$this->response->setData(Ticket_message::convertContent($inputs['content'], $inputs['format']), 'content');
		$this->response->setStatus(true);

		return $this->response;
	}

	public function getTemplate(array $data): Response
	{
		Authorization::haveOrFail('use_templates');

		$canAdd = Authorization::is_accessed('add');
		$canReply = Authorization::is_accessed('reply');

		if (!$canAdd and $canReply) {
			throw new NotFound();
		}

		$query = new Template();
		
		$parenthesis = new Parenthesis();
		$parenthesis->where('message_type', NULL, 'is');
		if ($canAdd) {
			$parenthesis->orWhere('message_type', Template::ADD);
		}
		if ($canReply) {
			$parenthesis->orWhere('message_type', Template::REPLY);
		}

		$query->where($parenthesis);
		$query->where('id', $data['id']);

		$template = $query->getOne();

		if (!$template) {
			throw new NotFound();
		}

		$this->response->setData([
			'data' => [
				'message_format' => $template->getMessageFormat(),
				'subject' => [
					'value' => $template->getSubject(),
					'variables' => $template->getSubject() ? Template::extractVariables($template->getSubject()) : [],
				],
				'content' => [
					'value' => $template->getContent(),
					'variables' => Template::extractVariables($template->getContent()),
				],
			],
		]);
		$this->response->setStatus(true);

		return $this->response;
	}
}
