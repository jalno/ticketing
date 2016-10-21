<?php
namespace packages\ticketing\controllers;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\NotFound;
use \packages\base\http;
use \packages\base\db;
use \packages\base\IO;
use \packages\base\packages;
use \packages\base\views\FormError;
use \packages\base\inputValidation;
use \packages\base\response\file as responsefile;

use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;

use \packages\ticketing\controller;
use \packages\ticketing\authorization;
use \packages\userpanel\authentication;

use \packages\ticketing\view;
use \packages\ticketing\ticket;
use \packages\ticketing\department;
use \packages\ticketing\ticket_message;
use \packages\ticketing\ticket_param;
use \packages\ticketing\products;
use \packages\ticketing\ticket_file;

class ticketing extends controller{
	protected $authentication = true;
	private function checkTicket($ticketID){
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::where("ticketing_tickets.id", $ticketID);
		if($ticket = new ticket(db::getOne("ticketing_tickets", "ticketing_tickets.*"))){
			return $ticket;
		}else{
			throw new NotFound;
		}
	}
	private function checkTicketMessage($messageID){
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets_msgs.user", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::where("ticketing_tickets_msgs.id", $messageID);

		if($ticket_message = new ticket_message(db::getOne("ticketing_tickets_msgs", "ticketing_tickets_msgs.*"))){
			return $ticket_message;
		}else{
			throw new NotFound;
		}
	}
	public function index(){
		authorization::haveOrFail('list');
		$view = view::byName("\\packages\\ticketing\\views\\ticketlist");
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::orderBy('id', ' DESC');
		db::pageLimit($this->items_per_page);
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
				'type' => 'email',
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
			)
		);
		$this->response->setStatus(false);
		if(http::is_post()){
			try{
				$inputs = $this->checkinputs($inputsRules);
				if(empty($inputs)){
					throw new inputValidation("search");
				}
				if(isset($inputs['id']) and $inputs['id']){
					db::where('ticketing_tickets.id', $inputs['id']);
				}
				if(isset($inputs['title']) and $inputs['title']){
					db::where('ticketing_tickets.title', $inputs['title'], "%");
				}
				if(isset($inputs['client']) and $inputs['client']){
					db::where('userpanel_users.email', $inputs['client']);
				}
				if(isset($inputs['status']) and $inputs['status']){
					db::where('ticketing_tickets.status', $inputs['status']);
				}
				if(isset($inputs['priority']) and $inputs['priority']){
					db::where('ticketing_tickets.priority', $inputs['priority']);
				}
				if(isset($inputs['department']) and $inputs['department']){
					db::where('ticketing_tickets.department', $inputs['department']);
				}
				$this->response->setStatus(true);
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
			$tickeetData = db::paginate("ticketing_tickets", $this->page, array("ticketing_tickets.*"));

			$tickets = array();
			foreach($tickeetData as $ticket){
				$tickets[] = new ticket($ticket);
			}
			$view->setTickets($tickets);
		}else{
			$this->response->setStatus(true);
			$tickeetData = db::paginate("ticketing_tickets", $this->page, array("ticketing_tickets.*"));

			$tickets = array();
			foreach($tickeetData as $ticket){
				$tickets[] = new ticket($ticket);
			}
			$view->setTickets($tickets);
		}
		$view->setDepartment(department::get());
		$this->response->setView($view);
		return $this->response;
	}
	public function add(){
		$view = view::byName("\\packages\\ticketing\\views\\add");
		authorization::haveOrFail('add');
		$children = authorization::childrenTypes();
		$view->setDepartmentData(department::get());
		$view->setProducts(products::get());
		if($children){
			$view->setData(true, 'selectclient');
		}
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
					'type' => 'number'
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
				if(isset($inputs['product']) and $inputs['product']){
					$inputs['product'] = products::getOne($inputs['product']);
					if(!$inputs['product']){
						throw new inputValidation("product");
					}
				}else{
					$inputs['product'] = null;
				}
				if($inputs['product']){
					if(isset($inputs['service']) and $inputs['service']){
						$inputs['service'] = $inputs['product']->getServiceById($inputs['service']);
						if(!$inputs['service']){
							throw new inputValidation("service");
						}
					}else{
						throw new inputValidation("service");
					}
				}

				$ticket = new ticket();
				$ticket->title	= $inputs['title'];
				$ticket->priority = $inputs['priority'];
				$ticket->client = $inputs['client']->id;
				$ticket->department = $inputs['department']->id;
				$ticket->status = ticket::unread;
				if(isset($inputs['product'], $inputs['service']) and $inputs['product'] and $inputs['service']){
					$ticket->setParam('product', $inputs['product']->getName());
					$ticket->setParam('service', $inputs['service']->getId());
				}
				$ticket->save();

				$message = new ticket_message();

				$message->ticket = $ticket->id;
				$message->text = $inputs['text'];
				$message->user = authentication::getID();
				$message->status = ticket::unread;

				$message->save();
				if(isset($inputs['product'])){
					$ticket->setParam('product', $inputs['product']);
					$ticket->setParam('service', $inputs['service']);
				}
				if(isset($inputs['file'])){
					if($inputs['file']['error'] == 0){
						$name = md5_file($inputs['file']['tmp_name']);
						$directory = packages::package('ticketing')->getFilePath('storage/private');
						if(!is_dir($directory)){
							IO\mkdir($directory);
						}
						if(move_uploaded_file($inputs['file']['tmp_name'], $directory.'/'.$name)){
							$message->addFile(array(
								'name' => $inputs['file']['name'],
								'size' => $inputs['file']['size'],
								'path' => 'private/'.$name,
							));
						}else{
							throw new inputValidation("file");
						}
					}elseif($inputs['file']['error'] != 4){
						throw new inputValidation("file_status");
					}
				}
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
		$view->setTicketData($ticket);
		if(http::is_post()){
			authorization::haveOrFail('reply');
			$inputsRules = array(
				'text' => array(
					'type' => 'string',
				),
				'file' => array(
					'type' => 'file',
					'optional' =>true,
					'empty' => true
				)
			);
			$this->response->setStatus(false);
			try {
				if(!$ticket->param('ticket_lock')){

					$inputs = $this->checkinputs($inputsRules);
					$ticket_message = new ticket_message();

					$ticket_message->ticket = $ticket->id;
					$ticket_message->date = time();
					$ticket_message->user = authentication::getID();
					$ticket_message->text = $inputs['text'];
					$ticket_message->status = ticket_message::unread;
					$ticket_message->save();

					if(isset($inputs['file'])){
						if($inputs['file']['error'] == 0){
							$name = md5_file($inputs['file']['tmp_name']);

							$directory = packages::package('ticketing')->getFilePath('storage/private');
							if(!is_dir($directory)){
								IO\mkdir($directory);
							}
							if(move_uploaded_file($inputs['file']['tmp_name'], $directory.'/'.$name)){
								$ticket_message->addFile(array(
									'name' => $inputs['file']['name'],
									'size' => $inputs['file']['size'],
									'path' => 'private/'.$name,
								));
							}else{
								throw new inputValidation("file");
							}
						}elseif($inputs['file']['error'] != 4){
							throw new inputValidation("file_status");
						}
					}

					$ticket->status = ticket::answered;
					$ticket->reply_at = date::time();
					$ticket->save();
					$this->response->Go(userpanel\url('ticketing/view/'.$data['ticket']));

					$this->response->setStatus(true);
				}else{
					throw new inputValidation("ticket_lock");
				}
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
		}else{
			if($ticket->status == ticket::unread){
				$ticket->status = ticket::read;
				$ticket->save();
			}
			if($ticket->client->id == authentication::getID()){
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
			$this->response->Go(userpanel\url('ticketing/view/'.$ticket));
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

		$this->response->setStatus(false);
		if(http::is_post()){
			$inputsRules = array(
				'text' => array(
					'type' => 'string',
				)
			);
			try {
				$inputs = $this->checkinputs($inputsRules);

				$ticket_message->text = $inputs['text'];
				$ticket_message->save();
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('ticketing/view/'.$ticket_message->ticket));
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
		}else{
			$this->response->setStatus(true);
		}
		$view->setMessageData($ticket_message);
		$this->response->setView($view);
		return $this->response;
	}
	public function edit($data){
		$view = view::byName("\\packages\\ticketing\\views\\edit");
		authorization::haveOrFail('edit');

		$ticket = $this->checkTicket($data['ticket']);
		$view->setDepartmentData(department::get());
		$view->setTicketData($ticket);
		if(http::is_post()){
			$this->response->setStatus(false);
			try {
				$inputsRules = array(
					'title' => array(
						'type' => 'string',
					),
					'priority' => array(
						'type' => 'number',
						'values' => array(ticket::instantaneous, ticket::important, ticket::ordinary)
					),
					'department' => array(
						'type' => 'number'
					),
					'client' => array(
						'type' => 'number'
					),
					'status' => array(
						'type' => 'number',
						'values' => array(
							ticket::unread,
							ticket::read,
							ticket::answered,
							ticket::in_progress,
							ticket::closed
						)
					)
				);
				$inputs = $this->checkinputs($inputsRules);

				if($user = user::byId($inputs['client'])){
					if($department = department::byId($inputs['department'])){
						$ticket->title = $inputs['title'];
						$ticket->priority = $inputs['priority'];
						$ticket->department = $department->id;
						$ticket->client = $user->id;
						$ticket->status = $inputs['status'];
						$ticket->save();
						$this->response->setStatus(true);
						$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id ));
					}
				}
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
		}else{
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
		authorization::haveOrFail('lock');

		$ticket = $this->checkTicket($data['ticket']);
		$view->setTicketData($ticket);
		$this->response->setStatus(false);
		if(http::is_post()){
			$param = ticket_param::where('ticket', $ticket->id)->where('name', 'ticket_lock')->getOne();
			$param->delete();
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
		db::join("ticketing_tickets_msgs", "ticketing_tickets_msgs.id=ticketing_files.message", 'left');
		db::where("ticketing_files.id", $data['file']);
		if($fileData = db::getOne("ticketing_files", array("ticketing_files.*"))){
			$file = new ticket_file($fileData);
			if(($fopen  = @fopen(packages::package('ticketing')->getFilePath('storage/'.$file->path), 'r')) !== false){
				$responsefile = new responsefile();
				$responsefile->setStream($fopen);
				$responsefile->setSize($file->size);
				$responsefile->setName($file->name);
				$this->response->setFile($responsefile);
				return $this->response;
			}else{
				throw new NotFound;
			}
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
					'type' => 'number'
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
}
