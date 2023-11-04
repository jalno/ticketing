<?php
namespace themes\clipone\views\ticketing;
use \packages\base\translator;
use \packages\ticketing\views\edit as ticketEdit;
use \packages\ticketing\ticket;
use packages\ticketing\Label;
use \packages\userpanel;
use \packages\userpanel\user;
use \themes\clipone\views\formTrait;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
class edit extends ticketEdit
{
	use viewTrait, formTrait;

	public Ticket $ticket;

	public function __beforeLoad(){
		$this->ticket = $this->getTicket();
		$this->setTitle(array(
			translator::trans('ticketing.edit'),
			translator::trans('ticket'),
			"#".$this->ticket->id
		));
		$this->setShortDescription(translator::trans('ticketing.edit').' '.translator::trans('ticket'));
		$this->setNavigation();
		$this->setFormData();
	}

	public function export(): array
	{
		$ticket = $this->getTicket();

		$data = [
			'ticket' => $ticket->toArray(),
		];

		$data['ticket']['client'] = [
			'id' => $ticket->client->id,
			'name' => $ticket->client->name,
			'lastname' => $ticket->client->lastname,
		];

		if ($ticket->operator) {
			$data['ticket']['operator'] = [
				'id' => $ticket->operator->id,
				'name' => $ticket->operator->name,
				'lastname' => $ticket->operator->lastname,
			];
		}

		if ($ticket->labels) {
			$data['ticket']['labels'] = array_map(fn (Label $label) => [
				'id' => $label->getID(),
				'title' => $label->getTitle(),
				'description' => $label->getDescription() ?? '',
				'color' => $label->getColor(),
			], $ticket->labels);
		}

		return ['data' => $data];
	}

	private function setFormData(){
		if($user = $this->getDataForm('client')){
			if($user = user::byId($user)){
				$this->setDataForm($user->getFullName(), 'client_name');
			}
		}
	}
	private function setNavigation(){
		navigation::active("ticketing/list");
	}
	protected function getDepartmentForSelect(){
		$departments = [];
		foreach($this->getDepartment() as $department){
			$departments[] = array(
				'title' => $department->title,
				'value' => $department->id
			);
		}
		return $departments;
	}
	protected function getStatusForSelect(){
		return array(
			array(
	            'title' => translator::trans('unread'),
	            'value' => ticket::unread
        	),
			array(
	            'title' => translator::trans('read'),
	            'value' => ticket::read
        	),
			array(
	            'title' => translator::trans('answered'),
	            'value' => ticket::answered
        	),
			array(
	            'title' => translator::trans('in_progress'),
	            'value' => ticket::in_progress
        	),
			array(
	            'title' => translator::trans('closed'),
	            'value' => ticket::closed
        	)
		);
	}
	protected function getpriortyForSelect(){
		return array(
			array(
	            'title' => translator::trans('instantaneous'),
	            'value' => ticket::instantaneous
        	),
			array(
	            'title' => translator::trans('important'),
	            'value' => ticket::important
        	),
			array(
	            'title' => translator::trans('ordinary'),
	            'value' => ticket::ordinary
        	)
		);
	}
}
