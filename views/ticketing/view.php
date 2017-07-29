<?php
namespace packages\ticketing\views;
use \packages\base\packages;
use \packages\base\frontend\theme;
use \packages\userpanel\user;
use \packages\ticketing\ticket;
use \packages\ticketing\views\form;
use \packages\ticketing\authorization;
class view extends form{
	protected $canEdit;
	protected $canEditMessage;
	protected $canDel;
	protected $canDelMessage;
	protected $canClose;
	static protected $navigation;
	function __construct(){
		$this->canEdit = authorization::is_accessed('edit');
		$this->canDel = authorization::is_accessed('delete');
		$this->canEditMessage = authorization::is_accessed('message_edit');
		$this->canDelMessage = authorization::is_accessed('message_delete');
		$this->canViewDec = authorization::is_accessed('view_description');
		$this->canClose = authorization::is_accessed('close');
	}
	public function setTicket(ticket $ticket){
		$this->setData($ticket, 'ticket');
	}
	public function getTicket(){
		return $this->getData('ticket');
	}
	protected function getUserAvatar(user $user){
		return $user->avatar ? packages::package('userpanel')->url($user->avatar) : theme::url('assets/images/user.png');
	}
}
