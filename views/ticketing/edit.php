<?php
namespace packages\ticketing\views;
use \packages\ticketing\authorization;
class edit extends \packages\ticketing\views\form{
	public function setTicketData($data){
		$this->setData($data, 'ticket');
	}
	public function getTicketData(){
		return $this->getData('ticket');
	}
	public function setDepartmentData($data){
		$this->setData($data, 'department');
	}
	public function getDepartmentData(){
		return $this->getData('department');
	}
}
