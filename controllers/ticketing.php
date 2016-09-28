<?php
namespace packages\ticketing\controllers;
use \packages\base;
use \packages\base\NotFound;
use \packages\base\http;
use \packages\base\db;
use \packages\base\views\FormError;
use \packages\base\inputValidation;

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

class ticketing extends controller{
	protected $authentication = true;
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
	public function view($data){
		$view = view::byName("\\packages\\ticketing\\views\\view");

		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::where("ticketing_tickets.id", $data['ticket']);
		$ticket = new ticket(db::getOne("ticketing_tickets", "ticketing_tickets.*"));
		if($ticket->id){
			$view->setTicketData($ticket);
			if(http::is_post()){
				$inputsRules = array(
					'text' => array(
						'type' => 'string',
					)
				);
				$this->response->setStatus(false);
				try {
					if($ticket->param('ticket_lock') === false or $ticket->param('ticket_lock') == ticket::canSendMessage){
						authorization::haveOrFail('reply');
						$inputs = $this->checkinputs($inputsRules);

						$ticket_message = new ticket_message();

						$ticket_message->ticket = $ticket->id;
						$ticket_message->date = time();
						$ticket_message->user = authentication::getID();
						$ticket_message->text = $inputs['text'];
						$ticket_message->status = 0;

						if($ticket_message->save()){
							$ticket->status = ticket::answered;
							$ticket->save();
							$this->response->Go(userpanel\url('ticketing/view/'.$data['ticket']));
						}

						$this->response->setStatus(true);
					}else{
						throw new inputValidation("ticket_lock");
					}
				}catch(inputValidation $error){
					$view->setFormError(FormError::fromException($error));
				}
			}else{
				authorization::haveOrFail('view');
				if($ticket->status == ticket::unread){
					$ticket->status = ticket::read;
					$ticket->save();
				}
				$this->response->setStatus(true);
			}
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function message_delete($data){
		$view = view::byName("\\packages\\ticketing\\views\\message_delete");
		authorization::haveOrFail('message_delete');
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets_msgs.user", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::where("ticketing_tickets_msgs.id", $data['ticket']);

		$ticket_message = new ticket_message(db::getOne("ticketing_tickets_msgs", "ticketing_tickets_msgs.*"));
		$view->setMessageData($ticket_message);
		if(http::is_post()){
			if($ticket_message){
				$ticket = $ticket_message->ticket;
				if($ticket_message->delete()){
					$this->response->Go(userpanel\url('ticketing/view/'.$ticket));
				}
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
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets_msgs.user", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::where("ticketing_tickets_msgs.id", $data['ticket']);

		$ticket_message = new ticket_message(db::getOne("ticketing_tickets_msgs", "ticketing_tickets_msgs.*"));

		$inputsRules = array(
			'text' => array(
				'type' => 'string',
			)
		);
		$this->response->setStatus(false);
		if(http::is_post()){
			if($ticket_message){
				$inputs = $this->checkinputs($inputsRules);
				$ticket_message->text = $inputs['text'];
				if($ticket_message->save()){
					$this->response->setStatus(true);
					$this->response->Go(userpanel\url('ticketing/view/'.$ticket_message->ticket));
				}
			}
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
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::where("ticketing_tickets.id", $data['ticket']);

		$ticket = new ticket(db::getOne("ticketing_tickets", "ticketing_tickets.*"));
		$view->setTicketData($ticket);
		$view->setDepartmentData(department::get());
		if($ticket){
			$inputsRules = array(
				'title' => array(
					'type' => 'string',
				),
				'priority' => array(
					'type' => 'number',
					'value' => array(1, 2, 3)
				),
				'department' => array(
					'type' => 'number',
					'values' => array(1, 2, 3, 4, 5)
				),
				'client' => array(
					'type' => 'number'
				),
				'status' => array(
					'type' => 'number',
					'values' => array(1, 2, 3, 4, 5)
				)
			);
			$this->response->setStatus(false);
			if(http::is_post()){
				try {
					$inputs = $this->checkinputs($inputsRules);
					if($user = user::byId($inputs['client'])){
						$ticket->title = $inputs['title'];
						$ticket->priority = $inputs['priority'];
						$ticket->department = $inputs['department'];
						$ticket->client = $inputs['client'];
						$ticket->status = $inputs['status'];

						if($ticket->save()){
							$this->response->setStatus(true);
							$this->response->Go(userpanel\url('ticketing'));
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
		}else{
			throw new NotFound;
		}
	}
	public function lock($data){
		$view = view::byName("\\packages\\ticketing\\views\\lock");
		authorization::haveOrFail('lock');
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::where("ticketing_tickets.id", $data['ticket']);

		$ticket = new ticket(db::getOne("ticketing_tickets", "ticketing_tickets.*"));
		$view->setTicketData($ticket);
		if($ticket){
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
		}else{
			throw new NotFound;
		}
	}
	public function unlock($data){
		$view = view::byName("\\packages\\ticketing\\views\\unlock");
		authorization::haveOrFail('lock');
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::where("ticketing_tickets.id", $data['ticket']);

		$ticket = new ticket(db::getOne("ticketing_tickets", "ticketing_tickets.*"));
		$view->setTicketData($ticket);
		if($ticket){
			$this->response->setStatus(false);
			if(http::is_post()){
				try {
					$param = ticket_param::where('ticket', $ticket->id)->where('name', 'ticket_lock')->getOne();
					if($param->delete()){
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
		}else{
			throw new NotFound;
		}
	}
	public function delete($data){
		$view = view::byName("\\packages\\ticketing\\views\\delete");
		authorization::haveOrFail('delete');
		$types = authorization::childrenTypes();
		db::join("userpanel_users", "userpanel_users.id=ticketing_tickets.client", "LEFT");
		if($types){
			db::where("userpanel_users.type", $types, 'in');
		}else{
			db::where("userpanel_users.id", authentication::getID());
		}
		db::where("ticketing_tickets.id", $data['ticket']);

		$ticket = new ticket(db::getOne("ticketing_tickets", "ticketing_tickets.*"));
		if($ticket){
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
		}else{
			throw new NotFound;
		}
	}
}
