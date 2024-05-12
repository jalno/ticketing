<?php
namespace packages\ticketing\Listeners;
use \packages\sms\Template;
use \packages\sms\Events\Templates;
class SMS{
	public function templates(Templates $event){
		$event->addTemplate($this->newTicketTemplate());
		$event->addTemplate($this->replyTicketTemplate());
	}
	private function newTicketTemplate(){
		$template = new Templates();
		$template->name = 'ticketing_ticket_add';
		$template->event = 'packages\ticketing\events\tickets\add';
		$template->addVariable('\\packages\\ticketing\\ticket_message');
		return $template;
	}
	private function replyTicketTemplate(){
		$template = new Templates();
		$template->name = 'ticketing_ticket_reply';
		$template->event = 'packages\ticketing\events\tickets\reply';
		$template->addVariable('\\packages\\ticketing\\ticket_message');
		return $template;
	}
}
