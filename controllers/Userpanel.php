<?php
namespace packages\ticketing\controllers;

use packages\base\{db, InputValidationException, db\Parenthesis, Response, NotFound, View};
use packages\ticketing\{Controller, Authorization, Department, Ticket};
use packages\userpanel\{Authentication, User};

class Userpanel extends controller {
	protected $authentication = true;
	public function operators($data): response {
		authorization::haveOrFail("edit");
		$department = department::byId($data["department"]);
		if (! $department) {
			throw new NotFound();
		}
		$inputs = $this->checkinputs(array(
			"word" => array(),
		));
		$this->response->setStatus(true);
		$users = $department->users;
		$priority = db::subQuery();
		$priority->setQueryOption("DISTINCT");
		$priority->get("userpanel_usertypes_priorities", null, "parent");
		$permission = db::subQuery();
		$permission->where("name", "ticketing_view");
		$permission->get("userpanel_usertypes_permissions", null, "type");
		$model = new user();
		$model->where("type", $priority, "IN");
		$model->where("type", $permission, "IN");
		if ($users) {
			$model->where("id", $users, "IN");
		}
		$parenthesis = new parenthesis();
		foreach (array("name", "lastname", "email", "cellphone") as $item) {
			$parenthesis->orWhere($item, $inputs["word"], "contains");
		}
		$parenthesis->orWhere("CONCAT(`name`, ' ', `lastname`)", $inputs["word"], "contains");
		$model->where($parenthesis);
		$this->response->setData($model->arrayBuilder()->get(), "items");
		return $this->response;
	}

	public function getUserTickets($data) {
		Authorization::haveOrFail('list');
		if (!is_numeric($data['id']) or $data['id'] == Authentication::getID()) {
			throw new NotFound();
		}
		$types = Authorization::childrenTypes();
		if (!$types) {
			throw new NotFound();
		}
		$user = User::where('id', $data['id'])->where('type', $types, 'IN')->getOne();
		if (!$user) {
			throw new NotFound();
		}
		$view = View::byName(\themes\clipone\views\users\View::class);
		$this->response->setView($view);
		$view->setData($user, 'user');
		$view->isTab(true);
		$view->triggerTabs();
		$view->activeTab('ticket');
		$view->setDepartment((new Department)->where('status', Department::ACTIVE)->get());
		$inputs = $this->checkinputs(array(
			'id' => array(
				'type' => 'number',
				'optional' => true,
			),
			'title' => array(
				'type' => 'string',
				'optional' => true,
			),
			'status' => array(
				'type' => 'string',
				'optional' => true,
				'default' => implode(',', array(Ticket::unread, Ticket::read, Ticket::answered, Ticket::in_progress)),
			),
			'priority' => array(
				'type' => 'number',
				'values' => Ticket::PRIORITIES,
				'optional' => true,
			),
			'department' => array(
				'type' => Department::class,
				'query' => function ($query) {
					$query->where('status', Department::ACTIVE);
				},
				'optional' => true,
			),
			'word' => array(
				'type' => 'string',
				'optional' => true,
			),
			'comparison' => array(
				'values' => array('equals', 'startswith', 'contains'),
				'default' => 'contains',
				'optional' => true
			)
		));
		$ticket = new Ticket();
		$ticket->with('client');
		$ticket->with('department');
		$ticket->where('ticketing_tickets.client', $user->id);
		if (isset($inputs['status']) and $inputs['status']) {
			$statuses = explode(',', $inputs['status']);
			foreach ($statuses as $status) {
				if (!in_array($status, array(Ticket::unread, Ticket::read, Ticket::answered, Ticket::in_progress, Ticket::closed))) {
					throw new InputValidationException('status');
				}
			}
			$ticket->where('ticketing_tickets.status', $statuses, 'IN');
			$view->setDataForm($statuses, 'status');
		}
		foreach (array('id', 'title', 'title', 'priority', 'department') as $item) {
			if (isset($inputs[$item]) and $inputs[$item]) {
				$comparison = $inputs['comparison'];
				if (in_array($item, array('id', 'status', 'department'))) {
					$comparison = 'equals';
				}
				if ($item == 'department') {
					$inputs[$item] = $inputs[$item]->id;
				}
				$ticket->where("ticketing_tickets.{$item}", $inputs[$item], $comparison);
			}
		}
		if (isset($inputs['word']) and $inputs['word']) {
			$parenthesis = new Parenthesis();
			foreach (array('title') as $item) {
				if (!isset($inputs[$item]) or !$inputs[$item]) {
					$parenthesis->where("ticketing_tickets.{$item}", $inputs['word'], $inputs['comparison'], 'OR');
				}
			}
			db::join('ticketing_tickets_msgs', 'ticketing_tickets_msgs.ticket=ticketing_tickets.id', 'LEFT');
			db::join('ticketing_files', 'ticketing_files.message=ticketing_tickets_msgs.id', 'LEFT');
			$parenthesis->orWhere('ticketing_tickets_msgs.text', $inputs['word'], $inputs['comparison']);
			$parenthesis->orWhere('ticketing_files.name', $inputs['word'], $inputs['comparison']);
			$ticket->where($parenthesis);
			$ticket->setQueryOption('DISTINCT');
			$view->setDataForm($inputs['word'], 'word');
		}
		$ticket->orderBy('ticketing_tickets.reply_at', 'DESC');
		$ticket->pageLimit = $this->items_per_page;
		$tickets = $ticket->paginate($this->page, array(
			'ticketing_tickets.*',
			'userpanel_users.*',
			'ticketing_departments.*',
		));
		$view->setDataList($tickets);
		$view->setPaginate($this->page, db::totalCount(), $this->items_per_page);
		$this->response->setStatus(true);
		return $this->response;
	}
}
