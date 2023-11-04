<?php
namespace themes\clipone\views\ticketing;

use packages\userpanel;
use packages\base\{view\Error, views\traits\Form, Translator, HTTP};
use themes\clipone\{navigation\MenuItem, Navigation, ViewTrait};
use themes\clipone\views\{FormTrait, ListTrait, TabTrait, ticketing\LabelTrait};
use packages\ticketing\{Authentication, Authorization, Ticket, views\ticketlist as ticketListView};
use packages\ticketing\Label;

class ListView extends TicketListView
{
	use Form, ViewTrait, ListTrait, FormTrait, TabTrait, LabelTrait;

	protected $multiuser;
	protected $hasAccessToUsers = false;

	public static function onSourceLoad() {
		parent::onSourceLoad();
		if (parent::$navigation) {
			$item = new MenuItem("ticketing");
			$item->setTitle(t('ticketing'));
			$item->setURL(userpanel\url('ticketing'));
			$item->setIcon('clip-user-6');
			$item->setPriority(280);
			Navigation::addItem($item);
		}
	}

	public function __beforeLoad() {
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

	public function export(): array
	{
		$data = [
			'items' => array_map(function (Ticket $ticket) {
				$data = $ticket->toArray();
				$data['client'] = [
					'id' => $ticket->client->id,
					'name' => $ticket->client->name,
					'lastname' => $ticket->client->lastname,
				];

				if ($ticket->operator) {
					$data['operator'] = [
						'id' => $ticket->operator->id,
						'name' => $ticket->operator->name,
						'lastname' => $ticket->operator->lastname,
					];
				}

				return $data;
			}, $this->getDataList()),
			'items_per_page' => (int) $this->itemsPage,
			'current_page' => (int) $this->currentPage,
			'total_items' => (int) $this->totalItems,
		];

		if ($this->canViewLabels) {
			$data['labels'] = array_map(fn (Label $label) => $label->toArray(), $this->labels);
		}

		return ['data' => $data];
	}

	/**
	 * @return Label[]
	 */
	public function getLabels(array $ids): array
	{
		return array_filter($this->labels, fn (Label $label) => in_array($label->getID(), $ids));
	}

	public function getLabelsForShow(array $ids): string
	{
		return implode(" ", array_map(fn (Label $label) => $this->getLabel($label, $this->isTab ? 'users/tickets/'.$this->getNewTicketClientID() : 'ticketing'), $this->getLabels($ids)));
	}

	protected function getNewTicketURL(): string {
		$newTicketClientID = $this->getNewTicketClientID();
		$params = array();
		if ($newTicketClientID) {
			$params['client'] = $newTicketClientID;
		}
		$query = http_build_query($params);
		return userpanel\url('ticketing/new' . ($query ? '?' . $query : ''));
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

		return false;
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
		$params = array_merge(HTTP::$data, $params);
		unset($params['page']);
		return "?" . http_build_query($params);
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
}
