<?php
namespace packages\ticketing\views;

class delete extends \packages\ticketing\view{
	public function setTicketData($data){
		$this->setData($data, 'ticket');
	}
	public function getTicketData(){
		return $this->getData('ticket');
	}
}
