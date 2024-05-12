<?php
namespace packages\ticketing\Views;
use \packages\ticketing\Authorization;
use \packages\ticketing\Views\Form;
class MessageEdit extends Form {
	public function setMessageData($message){
		$this->setData($message, 'message');
		$this->setDataForm($message->toArray());
	}
	public function getMessageData(){
		return $this->getData('message');
	}
}
