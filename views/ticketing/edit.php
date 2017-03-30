<?php
namespace packages\ticketing\views;
use \packages\ticketing\ticket;
use \packages\ticketing\views\form;
class edit extends \packages\ticketing\views\form{
	public function setTicket(ticket $ticket){
		$this->setData($ticket, 'ticket');
		$this->setDataForm($ticket->toArray());
	}
	public function getTicket(){
		return $this->getData('ticket');
	}
	public function setDepartment($department){
		$this->setData($department, 'department');
	}
	public function getDepartment(){
		return $this->getData('department');
	}
}
