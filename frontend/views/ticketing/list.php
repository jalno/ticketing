<?php
namespace themes\clipone\views\ticketing;

use packages\userpanel;
use packages\base\{view\Error, views\traits\Form, Translator, HTTP};
use themes\clipone\{navigation\menuItem, Navigation, ViewTrait};
use themes\clipone\views\{FormTrait, ListTrait, TabTrait};
use packages\ticketing\{Authentication, Authorization, Ticket, views\ticketlist as ticketListView};

class listview extends ticketListView {
	use form, viewTrait, listTrait, formTrait, TabTrait;
	protected $multiuser;
	protected $hasAccessToUsers = false;

	public function __beforeLoad(){
		$this->setTitle(array(
			t("tickets")
		));
		$this->setButtons();
		$this->onSourceLoad();
		if ($this->isTab) {
			Navigation::active("users");
		} else {
			Navigation::active("ticketing/list");
		}
		$this->addBodyClass("tickets-search");
		$this->multiuser = (bool) Authorization::childrenTypes();
		$this->hasAccessToUsers = Authorization::is_accessed("users_list", "userpanel");
	}
	private function addNotFoundError(){
		$error = new error();
		$error->setType(error::NOTICE);
		$error->setCode('ticketing.ticket.notfound');
		$error->setData([
			[
				'type' => 'btn-teal',
				'txt' => translator::trans('ticketing.add'),
				'link' => userpanel\url('ticketing/new')
			]
		], 'btns');
		$this->addError($error);
	}
	public function setButtons(){
		$this->setButton('view', $this->canView, array(
			'title' => translator::trans('ticketing.view'),
			'icon' => 'fa fa-credit-card',
			'classes' => array('btn', 'btn-xs', 'btn-green')
		));
		$this->setButton('delete', $this->canDel, array(
			'title' => translator::trans('ticketing.delete'),
			'icon' => 'fa fa-times',
			'classes' => array('btn', 'btn-xs', 'btn-bricky')
		));
	}
	public static function onSourceLoad(){
		parent::onSourceLoad();
		if(parent::$navigation){
			$item = new menuItem("ticketing");
			$item->setTitle(translator::trans('ticketing'));
			$item->setURL(userpanel\url('ticketing'));
			$item->setIcon('clip-user-6');
			$item->setPriority(280);
			navigation::addItem($item);
		}
	}
	protected function getDepartmentsForSelect(){
		$departments = array();
		$departments[0] = array(
			'title' => translator::trans("choose"),
			'value' => ''
		);
		foreach($this->getDepartment() as $department){
			$departments[] = array(
				'title' => $department->title,
				'value' => $department->id
			);
		}
		return $departments;
	}
	protected function getPriortyForSelect(){
		return array(
			array(
				'title' => translator::trans("choose"),
				"value" => ''
			),
			array(
				'title' => translator::trans("instantaneous"),
				'value' => ticket::instantaneous
			),
			array(
				'title' => translator::trans("important"),
				'value' => ticket::important
			),
			array(
				'title' => translator::trans("ordinary"),
				'value' => ticket::ordinary
			)
		);
	}
	protected function getComparisonsForSelect(){
		return array(
			array(
				'title' => translator::trans('search.comparison.contains'),
				'value' => 'contains'
			),
			array(
				'title' => translator::trans('search.comparison.equals'),
				'value' => 'equals'
			),
			array(
				'title' => translator::trans('search.comparison.startswith'),
				'value' => 'startswith'
			)
		);
	}
	protected function isActive($item = "all"): bool {
		$status = $this->getDataForm("status");
		if ($status) {
			sort($status);
		} else {
			$status = array();
		}
		if ($item == "all") {
			static $allStatus;
			if (! $allStatus) {
				$allStatus = array(
					ticket::unread,
					ticket::read,
					ticket::in_progress,
					ticket::answered,
					ticket::closed,
				);
				sort($allStatus);
			}
			return $status == $allStatus;
		}
		if ($item == "inProgress") {
			return $status == array(
				ticket::in_progress,
			);
		}
		if ($item == "active") {
			static $activeStatus;
			if (! $activeStatus) {
				$activeStatus = array(
					ticket::unread,
					ticket::read,
					ticket::answered,
					ticket::in_progress,
				);
				sort($activeStatus);
			}
			return ($status == $activeStatus or (count($status) == 1 and in_array($status[0], [ticket::unread, ticket::read, ticket::answered])));
		}
		if ($item == "closed") {
			return $status == array(
				ticket::closed,
			);
		}
	}
	protected function getOrderedTickets(): array {
		$tickets = $this->getTickets();
		if (! $tickets) {
			$tickets = array();
		}
		if (! authorization::childrenTypes()) {
			return $tickets;
		}
		$ordered = array();
		$user = authentication::getUser();
		foreach ($tickets as $key => $ticket) {
			if ($ticket->operator_id == $user->id) {
				$ordered[] = $ticket;
				unset($tickets[$key]);
			}
		}
		foreach ($tickets as $ticket) {
			$ordered[] = $ticket;
		}
		return $ordered;
	}
	protected function getTicketStatusForSelect(): array {
		return array(
			array(
				'title' => t("choose"),
				'value' => '',
				'disabled' => true,
			),
			array(
				'title' => t('unread'),
				'value' => Ticket::unread,
			),
			array(
				'title' => t('read'),
				'value' => Ticket::read,
			),
			array(
				'title' => t('in_progress'),
				'value' => Ticket::in_progress,
			),
			array(
				'title' => t('answered'),
				'value' => Ticket::answered,
			),
			array(
				'title' => t('closed'),
				'value' => Ticket::closed,
			),
		);
	}
	protected function getPath($params = []): string {
		return "?" . http_build_query(array_merge(HTTP::$data, $params));
	}
	/**
	 * Ouput the html file.
	 * 
	 * @return void
	 */
	public function output() {
		if ($this->isTab) {

			$this->outputTab();
		} else {
			parent::output();
		}
	}
}
