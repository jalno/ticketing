<?php
namespace packages\ticketing\listeners;
use \packages\sms\template;
use \packages\sms\events\templates;
class sms{
	public function templates(templates $event){
		$event->addTemplate($this->newTicketTemplate());
		$event->addTemplate($this->replyTicketTemplate());
	}
	private function newTicketTemplate(){
		$template = new template();
		$template->name = 'ticketing_ticket_add';
		$template->event = 'packages\ticketing\events\tickets\add';
		$template->addVariable('\\packages\\ticketing\\ticket_message');
		return $template;
	}
	private function replyTicketTemplate(){
		$template = new template();
		$template->name = 'ticketing_ticket_reply';
		$template->event = 'packages\ticketing\events\tickets\reply';
		$template->addVariable('\\packages\\ticketing\\ticket_message');
		return $template;
	}
}
