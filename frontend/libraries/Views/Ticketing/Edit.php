<?php
namespace themes\clipone\Views\Ticketing;
use \packages\base\Translator;
use \packages\ticketing\Views\Edit as TicketEdit;
use \packages\ticketing\Ticket;
use packages\ticketing\Label;
use \packages\userpanel;
use \packages\userpanel\User;
use \themes\clipone\Views\FormTrait;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
class Edit extends TicketEdit
{
	use ViewTrait, FormTrait;

	public Ticket $ticket;

	public function __beforeLoad(){
		$this->ticket = $this->getTicket();
		$this->setTitle(array(
			Translator::trans('ticketing.edit'),
			Translator::trans('ticket'),
			"#".$this->ticket->id
		));
		$this->setShortDescription(Translator::trans('ticketing.edit').' '.Translator::trans('ticket'));
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
			if($user = User::byId($user)){
				$this->setDataForm($user->getFullName(), 'client_name');
			}
		}
	}
	private function setNavigation(){
		Navigation::active("ticketing/list");
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
	            'title' => Translator::trans('unread'),
	            'value' => Ticket::unread
        	),
			array(
	            'title' => Translator::trans('read'),
	            'value' => ticket::read
        	),
			array(
	            'title' => Translator::trans('answered'),
	            'value' => Ticket::answered
        	),
			array(
	            'title' => Translator::trans('in_progress'),
	            'value' => Ticket::in_progress
        	),
			array(
	            'title' => Translator::trans('closed'),
	            'value' => Ticket::closed
        	)
		);
	}
	protected function getpriortyForSelect(){
		return array(
			array(
	            'title' => Translator::trans('instantaneous'),
	            'value' => Ticket::instantaneous
        	),
			array(
	            'title' => Translator::trans('important'),
	            'value' => Ticket::important
        	),
			array(
	            'title' => Translator::trans('ordinary'),
	            'value' => Ticket::ordinary
        	)
		);
	}
}
