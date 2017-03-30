<?php
namespace packages\ticketing\views;
use \packages\ticketing\ticket;
use \packages\ticketing\views\form;
use \packages\ticketing\authorization;
class view extends form{
	protected $canEdit;
	protected $canEditMessage;
	protected $canDel;
	protected $canDelMessage;
	static protected $navigation;
	function __construct(){
		$this->canEdit = authorization::is_accessed('edit');
		$this->canDel = authorization::is_accessed('delete');
		$this->canEditMessage = authorization::is_accessed('message_edit');
		$this->canDelMessage = authorization::is_accessed('message_delete');
		$this->canViewDec = authorization::is_accessed('view_description');
	}
	public function setTicket(ticket $ticket){
		$this->setData($ticket, 'ticket');
	}
	public function getTicket(){
		return $this->getData('ticket');
	}
}
