<?php
namespace packages\ticketing\controllers;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\NotFound;
use \packages\base\http;
use \packages\base\db;
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
		$tickeetData = db::paginate("ticketing_tickets", $this->page, array("ticketing_tickets.*"));
		$tickets = array();
		foreach($tickeetData as $ticket){
			$tickets[] = new ticket($ticket);
		}
		$view->setDataList($tickets);
		$view->setPaginate($this->page, $this->total_pages, $this->items_per_page);
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
		if($children){
			$view->setData(true, 'selectclient');
		}

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
					'value' => array(1, 2, 3, 4, 5)
				),
				'priority' => array(
					'type' => 'number',
					'value' => array(1, 2, 3)
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
				if(isset($inputs['client'])){
					if($user = user::byId($inputs['client'])){
						$client = $user->id;
					}else{
						throw new inputValidation("client");

					}
				}else{
					$client = authentication::getID();
				}

					$ticket = new ticket();

					$ticket->title = $inputs['title'];
					$ticket->priority = $inputs['priority'];
					$ticket->client = $client;
					$ticket->status = ticket::unread;


					if($department = department::byId($inputs['department'])){
						$ticket->department = $department->id;
					}else{
						throw new inputValidation("department");
					}


					$ticket->save();

					$message = new ticket_message();

					$message->ticket = $ticket->id;
					$message->text = $inputs['text'];
					$message->user = authentication::getID();
					$message->status = 0;

					$message->save();
					if(isset($inputs['product'])){
						$ticket->setParam('product', $inputs['product']);
						$ticket->setParam('service', $inputs['service']);
					}
					if(isset($inputs['file'])){
						if($inputs['file']['error'] == 0){
							$name = md5_file($inputs['file']['tmp_name']);
							$directory = __DIR__.'/../storage/'.$name;
							if(move_uploaded_file($inputs['file']['tmp_name'], $directory)){
								$message->addFile(array(
									'name' => $inputs['file']['name'],
									'size' => $inputs['file']['size'],
									'path' => $directory,
								));
							}else{
								throw new inputValidation("file");
							}
						}else{
							throw new \Exception("file_status");

						}
					}
					$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id));

			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
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
				if($ticket->param('ticket_lock') === false or $ticket->param('ticket_lock') == ticket::canSendMessage){

					$inputs = $this->checkinputs($inputsRules);
					$ticket_message = new ticket_message();

					$ticket_message->ticket = $ticket->id;
					$ticket_message->date = time();
					$ticket_message->user = authentication::getID();
					$ticket_message->text = $inputs['text'];
					$ticket_message->status = 0;
					$ticket_message->save();

					if(isset($inputs['file'])){
						if($inputs['file']['error'] == 0){
							$name = md5_file($inputs['file']['tmp_name']);
							$directory = __DIR__.'/../storage/'.$name;
							if(move_uploaded_file($inputs['file']['tmp_name'], $directory)){
								$ticket_message->addFile(array(
									'name' => $inputs['file']['name'],
									'size' => $inputs['file']['size'],
									'path' => $directory,
								));
							}else{
								throw new inputValidation("file");
							}
						}else{
							throw new \Exception("file_status");

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
				if($ticket_message->delete()){
					$this->response->Go(userpanel\url('ticketing/view/'.$ticket));
				}
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
		$inputsRules = array(
			'text' => array(
				'type' => 'string',
			)
		);
		$this->response->setStatus(false);
		if(http::is_post()){
			$inputs = $this->checkinputs($inputsRules);
			$ticket_message->text = $inputs['text'];
			$ticket_message->save();
			$this->response->setStatus(true);
			$this->response->Go(userpanel\url('ticketing/view/'.$ticket_message->ticket));
		}else{
			$view->setMessageData($ticket_message);
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function edit($data){
		$view = view::byName("\\packages\\ticketing\\views\\edit");
		authorization::haveOrFail('edit');

		$ticket = $this->checkTicket($data['ticket']);
		$view->setDepartmentData(department::get());
		$view->setTicketData($ticket);
		$status = array(ticket::unread, ticket::read, ticket::answered, ticket::in_progress, ticket::closed);
		$inputsRules = array(
			'title' => array(
				'type' => 'string',
			),
			'priority' => array(
				'type' => 'number',
				'values' => array(1, 2, 3)
			),
			'department' => array(
				'type' => 'number'
			),
			'client' => array(
				'type' => 'number'
			),
			'status' => array(
				'type' => 'number',
				'values' => $status
			)
		);
		$this->response->setStatus(false);
		if(http::is_post()){
			try {
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
						$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id   ));
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
			try {
				if($ticket->setParam('ticket_lock', 1)){
					$this->response->setStatus(true);
					$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id));
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
	public function unlock($data){
		$view = view::byName("\\packages\\ticketing\\views\\unlock");
		authorization::haveOrFail('lock');

		$ticket = $this->checkTicket($data['ticket']);
		$view->setTicketData($ticket);
		$this->response->setStatus(false);
		if(http::is_post()){
			try {
				$param = ticket_param::where('ticket', $ticket->id)->where('name', 'ticket_lock')->getOne();
				$param->delete();
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('ticketing/view/'.$ticket->id));
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
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
			try {
				$ticket->delete();
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('ticketing'));

			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function download($data){
		if(authorization::is_accessed('files_download')){

			db::join("ticketing_tickets_msgs", "ticketing_tickets_msgs.id=ticketing_files.message", 'left');
			db::where("ticketing_files.id", $data['file']);
			if($fileData = db::getOne("ticketing_files", array("ticketing_files.*"))){
				$file = new ticket_file($fileData);
				if(($fopen  = fopen($file->path, 'r')) !== false){
					$size = $file->size;
					$responsefile = new responsefile();
					$responsefile->setStream($fopen);
					$responsefile->setSize($size);
					$responsefile->setName($file->name);
					$this->response->setFile($responsefile);
					return $this->response;
				}

			}else{
				throw new NotFound;
			}
		}else{
			return authorization::FailResponse();
		}
	}
}
