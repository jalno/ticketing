<?php
namespace packages\ticketing\controllers;
use \packages\base\{IO, db, http, packages, NotFound, view\error, db\parenthesis, views\FormError, inputValidation, response\file as responsefile, translator};
use \packages\userpanel;
use \packages\userpanel\{user, date, log};
use \packages\ticketing\{controller, authorization, authentication, view, ticket, department, ticket_message, ticket_param, products, ticket_file, events, logs};

class ticketing extends controller{
	protected $authentication = true;
	private function checkTicket(int $ticketID) {
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		$ticket = new ticket();
		$ticket->where("ticketing_tickets.id", $ticketID);
		$ticket->with("department");
		$ticket = $ticket->getOne('ticketing_tickets.*');
		if(!$ticket or ($ticket->department->users and !in_array(authorization::getID(), $ticket->department->users))){
			throw new NotFound;
		}
		return $ticket;
	}
	private function checkTicketMessage($messageID) {
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets_msgs.user", "LEFT");
		$message = new ticket_message();
		if($types){
			$message->where("userpanel_users.type", $types, "in");
		}else{
			$message->where("userpanel_users.id", authentication::getID());
		}
		$message->where("ticketing_tickets_msgs.id", $messageID);
		$message->with("ticket");
		$message = $message->getOne("ticketing_tickets_msgs.*");
		if(!$message or ($message->ticket->department->users and !in_array(authentication::getID(), $message->ticket->department->users))){
			throw new NotFound;
		}
		return $message;
	}
	public function index(){
		authorization::haveOrFail('list');
		$view = view::byName("\\packages\\ticketing\\views\\ticketlist");
		$ticket = new ticket();
		$types = authorization::childrenTypes();
		if ($types) {
			db::join("ticketing_departments", "`ticketing_departments`.`id`=`ticketing_tickets`.`department`", "INNER");
			$parenthesis = new parenthesis();
			$parenthesis->where("JSON_SEARCH(`ticketing_departments`.`users`, 'one', " . authentication::getID() . ") ", null, "IS NOT");
			$parenthesis->orWhere("ticketing_departments.users ", null, "IS");
			db::joinWhere("ticketing_departments", $parenthesis);
		}
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "INNER");
		if ($types){
			$ticket->where("userpanel_users.type", $types, 'in');
		} else {
			$ticket->where("userpanel_users.id", authentication::getID());
		}
		$inputsRules = array(
			'id' => array(
				'type' => 'number',
				'optional' => true,
				'empty' => true
			),
			'title' => array(
				'type' => 'string',
				'optional' =>true,
				'empty' => true
			),
			'client' => array(
				'type' => 'number',
				'optional' => true,
				'empty' => true
			),
			'status' => array(
				'type' => 'number',
				'values' => array(ticket::unread, ticket::read, ticket::in_progress, ticket::closed, ticket::answered),
				'optional' => true,
				'empty' => true
			),
			'priority' => array(
				'type' => 'number',
				'values' => array(ticket::instantaneous, ticket::important, ticket::ordinary),
				'optional' => true,
				'empty' => true
			),
			'department' => array(
				'type' => 'number',
				'optional' => true,
				'empty' => true
			),
			'word' => array(
				'type' => 'string',
				'optional' => true,
				'empty' => true
			),
			'comparison' => array(
				'values' => array('equals', 'startswith', 'contains'),
				'default' => 'contains',
				'optional' => true
			)
		);
		$inputs = $this->checkinputs($inputsRules);
		foreach(array('id', 'title', 'status', 'client', 'title', 'priority', 'department') as $item){
			if(isset($inputs[$item]) and $inputs[$item]){
				$comparison = $inputs['comparison'];
				if(in_array($item, array('id', 'status', 'client'))){
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
			$parenthesis->where("ticketing_tickets_msgs.text", $inputs['word'], $inputs['comparison'], 'OR');
			$parenthesis->where("ticketing_files.name", $inputs['word'], $inputs['comparison'], 'OR');
			$ticket->where($parenthesis);
			db::join("ticketing_tickets_msgs", "ticketing_tickets_msgs.ticket=ticketing_tickets.id", "LEFT");
			db::join("ticketing_files", "ticketing_files.message=ticketing_tickets_msgs.id", "LEFT");
			$ticket->setQueryOption("DISTINCT");
		}
		$ticket->orderBy('ticketing_tickets.reply_at', 'DESC');
		$ticket->pageLimit = $this->items_per_page;
		$tickets = $ticket->paginate($this->page, 'ticketing_tickets.*');
		$view->setDataList($tickets);
		$view->setPaginate($this->page, db::totalCount(), $this->items_per_page);
		$view->setDepartment(department::get());
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function add(){
		$view = view::byName("\\packages\\ticketing\\views\\add");
		authorization::haveOrFail('add');
		$children = authorization::childrenTypes();
		$view->setDepartmentData(department::get());
		$view->setProducts(products::get());
		$this->response->setStatus(false);
		if(http::is_post()){
			$inputsRules = array(
				'title' => array(
					'type' => 'string',
				),
				'product' => array(
					'type' => 'string',
					'optional' =>true,
					'empty' => true
				),
				'service' => array(
					'type' => 'number',
					'optional' =>true,
					'empty' => true
				),
				'priority' => array(
					'type' => 'number',
					'value' => array(
						ticket::instantaneous,
						ticket::important,
						ticket::ordinary
					)
				),
				'department' => array(
					'type' => 'number',
				),
				'text' => array(
					'type' => 'string'
				),
				'file' => array(
					'type' => 'file',
					'optional' =>true,
					'empty' => true
				)
			);
			if($children){
				$inputsRules['client'] = array(
					'type' => 'number',
					'optional' => true
				);
			}
			try {
				$inputs = $this->checkinputs($inputsRules);
				$inputs['department'] = department::byId($inputs['department']);
				$inputs['client'] = isset($inputs['client']) ? user::byId($inputs['client']) : authentication::getUser();
				if(!$inputs['department']){
					throw new inputValidation("department");
				}
				if(!$inputs['client']){
					throw new inputValidation("client");
				}
				if (isset($inputs["product"], $inputs["service"])) {
					if ($inputs["product"] and $inputs["service"]) {
						$inputs["product"] = products::getOne($inputs["product"]);
						if (!$inputs["product"]) {
							throw new inputValidation("product");
						}
						$inputs["service"] = $inputs["product"]->getServiceById($inputs["client"], $inputs["service"]);
						if (!$inputs["service"]) {
							throw new inputValidation("service");
						}
					} else {
						unset($inputs["service"]);
						unset($inputs["product"]);
					}
				}
				if(isset($inputs['file'])){
					if(!is_array($inputs['file'])){
						throw new inputValidation("file");
					}
					if(empty($inputs['file'])){
						unset($inputs['file']);
					}
				}
				if(isset($inputs['file'])){
					$files = [];
					foreach($inputs['file'] as $file){
						if($file['error'] == 0){
							$files[] = $file;
						}elseif($file['error'] != 4){
							throw new inputValidation("file");
						}
					}
					$inputs['file'] = [];
					foreach($files as $file){
						$name = md5_file($file['tmp_name']);
						$directory = packages::package('ticketing')->getFilePath('storage/private');
						if(!is_dir($directory)){
							IO\mkdir($directory);
						}
						if(move_uploaded_file($file['tmp_name'], $directory.'/'.$name)){
							$inputs['file'][] = [
								'name' => $file['name'],
								'size' => $file['size'],
								'path' => 'private/'.$name,
							];
						}else{
							throw new inputValidation("file");
						}
					}
				}
				$ticket = new ticket();
				$ticket->title	= $inputs['title'];
				$ticket->priority = $inputs['priority'];
				$ticket->client = $inputs['client']->id;
				$ticket->department = $inputs['department']->id;
				$ticket->status = ((authentication::getID() == $inputs['client']->id) ? ticket::unread : ticket::answered);
				if (isset($inputs["product"], $inputs["service"])) {
					$ticket->setParam("product", $inputs["product"]->getName());
					$ticket->setParam("service", $inputs["service"]->getId());
				}
				$message = new ticket_message();
				if(isset($inputs['file'])){
					foreach($inputs['file'] as $file){
						$message->addFile($file);
					}
				}
				$ticket->save();
				$message->ticket = $ticket->id;
				$message->text = $inputs['text'];
				$message->user = authentication::getID();
				$message->status = ticket_message::unread;
				$message->save();
				$event = new events\tickets\add($message);
				$event->trigger();

				$log = new log();
				$log->user = authentication::getID();
				$log->title = translator::trans("ticketing.logs.add", ['ticket_id' => $ticket->id]);
				$log->type = logs\tickets\add::class;
				$log->save();

				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id));
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
			$view->setDataForm($this->inputsvalue($inputsRules));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function view($data){
		$view = view::byName("\\packages\\ticketing\\views\\view");
		authorization::haveOrFail('view');
		$ticket = $this->checkTicket($data['ticket']);
		$view->setTicket($ticket);
		if(!$ticket->department->isWorking()){
			$work = $ticket->department->currentWork();
			if($work->message){
				$error = new error;
				$error->setType(error::NOTICE);
				$error->setCode("ticketing.department.closed");
				$error->setMessage($work->message);
				$view->addError($error);
			}
		}
		$view->setDepartment(department::get());
		if(http::is_post()){
			authorization::haveOrFail('reply');
			$inputsRules = array(
				'text' => array(
				),
				'file' => array(
					'type' => 'file',
					'optional' =>true,
					'empty' => true
				)
			);
			$this->response->setStatus(false);
			try {
				if($ticket->param('ticket_lock')){
					throw new NotFound();
				}
				$inputs = $this->checkinputs($inputsRules);
				if(!$inputs['text'] = strip_tags($inputs['text'])){
					throw new inputValidation("text");
				}
				if(isset($inputs['file'])){
					if(!is_array($inputs['file'])){
						throw new inputValidation("file");
					}
					if(empty($inputs['file'])){
						unset($inputs['file']);
					}
				}
				if(isset($inputs['file'])){
					$files = [];
					foreach($inputs['file'] as $file){
						if($file['error'] == 0){
							$files[] = $file;
						}elseif($file['error'] != 4){
							throw new inputValidation("file");
						}
					}
					$inputs['file'] = [];
					foreach($files as $file){
						$name = md5_file($file['tmp_name']);
						$directory = packages::package('ticketing')->getFilePath('storage/private');
						if(!is_dir($directory)){
							IO\mkdir($directory);
						}
						if(move_uploaded_file($file['tmp_name'], $directory.'/'.$name)){
							$inputs['file'][] = [
								'name' => $file['name'],
								'size' => $file['size'],
								'path' => 'private/'.$name,
							];
						}else{
							throw new inputValidation("file");
						}
					}
				}
				$ticket_message = new ticket_message();
				$ticket_message->ticket = $ticket->id;
				$ticket_message->date = date::time();
				$ticket_message->user = authentication::getID();
				$ticket_message->text = $inputs['text'];
				$ticket_message->status = ticket_message::unread;
				if(isset($inputs['file'])){
					foreach($inputs['file'] as $file){
						$ticket_message->addFile($file);
					}
				}
				$ticket_message->save();
				$ticket->status = ((authorization::childrenTypes() and $ticket->client->id != $ticket_message->user->id) ? ticket::answered : ticket::unread);
				$ticket->reply_at = date::time();
				$ticket->save();
				$event = new events\tickets\reply($ticket_message);
				$event->trigger();
				$this->response->Go(userpanel\url('ticketing/view/'.$data['ticket']));
				$this->response->setStatus(true);
			}catch(inputValidation $e){
				$view->setFormError(FormError::fromException($e));
			}
		}else{
			$lastMSG = ticket_message::where("ticket", $ticket->id)->orderBy("date", "DESC")->getOne();
			if(authentication::getID() != $ticket->client->id){
				if($ticket->status == ticket::unread){
					$ticket->status = ticket::read;
					$ticket->save();
				}
			}else{
				foreach($ticket->message as $row){
					if($row->status == 0){
						$row->status = 1;
						$row->save();
					}
				}
			}
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function message_delete($data){
		$view = view::byName("\\packages\\ticketing\\views\\message_delete");
		authorization::haveOrFail('message_delete');

		$ticket_message = $this->checkTicketMessage($data['ticket']);
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
		$ticket_message = $this->checkTicketMessage($data['ticket']);
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
	public function edit($data){
		$view = view::byName("\\packages\\ticketing\\views\\edit");
		authorization::haveOrFail('edit');

		$ticket = $this->checkTicket($data['ticket']);
		$view->setDepartment(department::get());
		$view->setTicket($ticket);
		if(http::is_post()){
			$this->response->setStatus(false);
			try {
				$inputsRules = array(
					'title' => array(
						'type' => 'string',
						'optional' => true
					),
					'priority' => array(
						'type' => 'number',
						'values' => array(ticket::instantaneous, ticket::important, ticket::ordinary),
						'optional' => true
					),
					'department' => array(
						'type' => 'number',
						'optional' => true
					),
					'client' => array(
						'type' => 'number',
						'optional' => true
					),
					'status' => array(
						'type' => 'number',
						'values' => array(
							ticket::unread,
							ticket::read,
							ticket::answered,
							ticket::in_progress,
							ticket::closed
						),
						'optional' => true
					)
				);
				$inputs = $this->checkinputs($inputsRules);
				if(isset($inputs['client'])){
					if(!$inputs['client'] = user::byId($inputs['client'])){
						throw new inputValidation("client");
					}
				}
				if(isset($inputs['department'])){
					if(!$inputs['department'] = department::byId($inputs['department'])){
						throw new inputValidation("department");
					}
				}
				$parameters = ['oldData' => []];
				if(isset($inputs['status'])){
					$inputs['oldStatus'] = $ticket->status;
				}
				foreach(['title', 'priority', 'status'] as $item){
					if(isset($inputs[$item])){
						if($inputs[$item] != $ticket->$item){
							$parameters['oldData'][$item] = $ticket->$item;
							$ticket->$item = $inputs[$item];
						}
					}
				}
				foreach(['department', 'client'] as $item){
					if(isset($inputs[$item])){
						if($inputs[$item]->id != $ticket->$item->id){
							$parameters['oldData'][$item] = $ticket->$item;
							$ticket->$item = $inputs[$item]->id;
						}
					}
				}
				$ticket->save();
				if(isset($inputs['oldStatus'])){
					if($inputs['oldStatus'] != $ticket->status){
						if($ticket->status == ticket::closed){
							$event = new events\tickets\close($ticket);
							$event->trigger();
						}elseif($ticket->status == ticket::in_progress){
							$event = new events\tickets\inprogress($ticket);
							$event->trigger();
						}
					}
				}

				$log = new log();
				$log->user = authentication::getID();
				$log->title = translator::trans("ticketing.logs.edit", ['ticket_id' => $ticket->id]);
				$log->type = logs\tickets\edit::class;
				$log->parameters = $parameters;
				$log->save();

				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id ));
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
		}else{
			$inputsRules = array(
				'close' => array(
					'type' => 'string',
					'optional' => true,
					'empty' => true
				)
			);
			$inputs = $this->checkinputs($inputsRules);
			if(isset($inputs['close']) and $inputs['close']){
				if(strtolower($inputs['close']) == 'yes'){
					$view->setDataForm(ticket::closed, 'status');
				}
			}
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function lock($data){
		$view = view::byName("\\packages\\ticketing\\views\\lock");
		authorization::haveOrFail('lock');

		$ticket = $this->checkTicket($data['ticket']);
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

		$ticket = $this->checkTicket($data['ticket']);
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
		$ticket = $this->checkTicket($data['ticket']);
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
		authorization::haveOrFail('files_download');
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
		$ticket = $this->checkTicket($data['ticket']);
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
		$ticket = $this->checkTicket($data['ticket']);
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
		$ticket = $this->checkTicket($data['ticket']);
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
		$ticket = $this->checkTicket($data['ticket']);
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
}
