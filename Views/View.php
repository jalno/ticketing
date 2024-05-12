<?php
namespace packages\ticketing\Views;
use \packages\base\Packages;
use \packages\base\Frontend\Theme;
use \packages\userpanel\User;
use \packages\ticketing\Ticket;
use \packages\ticketing\Views\Form;
use \packages\ticketing\Authorization;
class View extends Form
{
	public bool $canViewDec;
	public bool $canViewLabels = false;

	protected $canEdit;
	protected $canEditMessage;
	protected $canDel;
	protected $canDelMessage;
	protected $canClose;
	protected $canEnableDisableNotification;
	static protected $navigation;
	function __construct(){
		$this->canEdit = Authorization::is_accessed('edit');
		$this->canDel = Authorization::is_accessed('delete');
		$this->canEditMessage = Authorization::is_accessed('message_edit');
		$this->canDelMessage = Authorization::is_accessed('message_delete');
		$this->canViewDec = Authorization::is_accessed('view_description');
		$this->canClose = Authorization::is_accessed('close');
		$this->canEnableDisableNotification = Authorization::is_accessed('enable_disabled_notification');
	}
	public function setTicket(Ticket $ticket){
		$this->setData($ticket, 'ticket');
		$this->setDataForm($ticket->toArray());
	}
	public function getTicket(){
		return $this->getData('ticket');
	}
	protected function getUserAvatar(User $user){
		return $user->avatar ? Packages::package('userpanel')->url($user->avatar) : Theme::url('assets/images/user.png');
	}
	public function setDepartment(array $departments){
		$this->setData($departments, 'departments');
	}
	protected function getDepartment():array{
		return $this->getData('departments');
	}
}
