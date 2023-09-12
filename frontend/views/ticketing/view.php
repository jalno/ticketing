<?php
namespace themes\clipone\views\ticketing;

use packages\base\Translator;
use packages\ticketing\Department;
use packages\ticketing\views\View as TicketView;
use packages\userpanel;
use packages\ticketing\{Authorization, Parsedown, Products, Ticket};
use packages\ticketing\Ticket_message as Message;
use themes\clipone\{BreadCrumb, Navigation, navigation\MenuItem, Utility, ViewTrait};
use themes\clipone\views\{FormTrait, ListTrait};
use themes\clipone\views\ticketing\HelperTrait;

class View extends TicketView
{
	use ViewTrait, ListTrait, FormTrait;
	use HelperTrait;

	public bool $sendNotification = false;
	public bool $canUseTemplates = false;
	public string $messageFormat = Message::html;

	protected $messages;
	protected $canSend = true;
	protected $isLocked = false;
	protected $ticket;
	protected $types = array();

	public function __construct()
	{
		parent::__construct();
		$this->canUseTemplates = Authorization::is_accessed('use_templates');
	}

	public function __beforeLoad()
	{
		$this->ticket = $this->getTicket();
		$this->sendNotification = Ticket::sendNotificationOnSendTicket($this->canEnableDisableNotification ? userpanel\Authentication::getUser() : null);
		$this->accessedDepartments = [$this->ticket->department];
		$this->messageFormat = userpanel\Authentication::getUser()->getOption('ticketing_editor') ?: Message::html;

		$this->setTitle([
			t('ticketing.view'),
			t('ticket'),
			"#".$this->ticket->id
		]);

		$this->setShortDescription(t('ticketing.view').' '.t('ticket'));
		$this->setNavigation();
		$this->SetDataView();
		$this->addBodyClass("ticketing");
		$this->addBodyClass("tickets-view");

		$this->types = Authorization::childrenTypes();
	}
	private function setNavigation(){
		$item = new menuItem("ticketing");
		$item->setTitle(t('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-paperplane');
		breadcrumb::addItem($item);

		$item = new menuItem("ticketing.view");
		$item->setTitle($this->ticket->title);
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('fa fa-comment-o');
		breadcrumb::addItem($item);
		navigation::active("ticketing/list");
	}
	protected function SetDataView(){
		$this->messages = $this->ticket->message;
		if($this->ticket->param('ticket_lock') or $this->ticket->param('ticket_lock') != ticket::canSendMessage){
			$this->isLocked = true;
		}
		$this->canSend = (
			Authorization::is_accessed("reply") and
			!$this->isLocked and
			$this->ticket->department->status == Department::ACTIVE
		);
		$this->setData(!$this->canSend, 'ticketing_editor_disabled');
		$this->setData(!$this->canUseTemplates, 'content_editor_preview_disabled');

		if (!$this->getDataForm('message_format')) {
			$this->setDataForm($this->messageFormat, 'message_format');
		}

		if($user = $this->getDataForm('client')){
			if($user = userpanel\user::byId($user)){
				$this->setDataForm($user->getFullName(), 'client_name');
			}
		}
		if($user = $this->getDataForm("operator_id")){
			if ($user = userpanel\user::byId($user)) {
				$this->setDataForm($user->getFullName(), "operator_name");
				$this->setDataForm($user->id, "operator");
			}
		}
		if($error = $this->getFormErrorsByInput('client')){
			$error->setInput('client_name');
			$this->setFormError($error);
		}
		$this->setDataForm($this->sendNotification ? 1 : 0, "send_notification");
	}

	protected function hasAccessToUser(userpanel\User $other): bool {

		$type = $other->data["type"];

		if ($type instanceof userpanel\Usertype) {
			$type = $type->id;
		}
		return in_array($type, $this->types);
	}

	protected function getProductService(){
		foreach(products::get() as $product){
			if($product->getName() == $this->ticket->param('product')){
				$product->showInformationBox($this->ticket->client, $this->ticket->param('service'));
				return $product;
			}
		}
		return null;
	}
	protected function getDepartmentForSelect(){
		$departments = [];
		foreach($this->getDepartment() as $department){
			$departments[] = [
				'title' => $department->title,
				'value' => $department->id
			];
		}
		return $departments;
	}
	protected function getStatusForSelect(){
		return [
			[
	            'title' => t('unread'),
	            'value' => ticket::unread
        	],
			[
	            'title' => t('read'),
	            'value' => ticket::read
        	],
			[
	            'title' => t('answered'),
	            'value' => ticket::answered
        	],
			[
	            'title' => t('in_progress'),
	            'value' => ticket::in_progress
        	],
			[
	            'title' => t('closed'),
	            'value' => ticket::closed
        	]
		];
	}
	protected function getpriortyForSelect(){
		return [
			[
	            'title' => t('instantaneous'),
	            'value' => ticket::instantaneous
        	],
			[
	            'title' => t('important'),
	            'value' => ticket::important
        	],
			[
	            'title' => t('ordinary'),
	            'value' => ticket::ordinary
        	]
		];
	}
}
