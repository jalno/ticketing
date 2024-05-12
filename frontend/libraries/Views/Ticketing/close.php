<?php
namespace themes\clipone\Views\Ticketing;
use \packages\base\Translator;
use \packages\ticketing\Views\Close as TicketClose;
use \packages\userpanel;
use \themes\clipone\Views\FormTrait;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \packages\ticketing\Ticket;
class Close extends TicketClose{
	use ViewTrait, FormTrait;
	protected $ticket;
	function __beforeLoad(){
		$this->ticket = $this->getTicket();
		$this->setTitle(array(
			Translator::trans('ticket.close')
		));
		$this->addBodyClass('ticket-close');
		$this->setNavigation();
	}
	private function setNavigation(){
		Navigation::active("ticketing/list");
	}
}
