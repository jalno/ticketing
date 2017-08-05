<?php
namespace packages\ticketing\views;
use \packages\ticketing\ticket;
use \packages\ticketing\views\form;
class inprogress extends form{
	public function setTicket(ticket $ticket){
		$this->setData($ticket, 'ticket');
	}
	public function getTicket():ticket{
		return $this->getData('ticket');
	}
}
