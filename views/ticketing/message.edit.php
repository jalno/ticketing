<?php
namespace packages\ticketing\views;
use \packages\ticketing\authorization;

class message_edit extends \packages\ticketing\view{
	protected $canDel;
	static protected $navigation;
	function __construct(){
		$this->canDel = authorization::is_accessed('edit');
	}
	public function setMessageData($data){
		$this->setData($data, 'message');
	}
	public function getMessageData(){
		return $this->getData('message');
	}
}
