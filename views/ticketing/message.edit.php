<?php
namespace packages\ticketing\views;
use \packages\ticketing\authorization;
use \packages\ticketing\views\form;
class message_edit extends form{
	public function setMessageData($message){
		$this->setData($message, 'message');
		$this->setDataForm($message->toArray());
	}
	public function getMessageData(){
		return $this->getData('message');
	}
}
