<?php
namespace themes\clipone\Views\Ticketing;
use \packages\base\Translator;
use \packages\ticketing\Views\Inprogress as TicketInProgress;
use \packages\userpanel;
use \themes\clipone\Views\FormTrait;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \packages\ticketing\Ticket;
class Inprogress extends TicketInProgress{
	use ViewTrait, FormTrait;
	protected $ticket;
	function __beforeLoad(){
		$this->ticket = $this->getTicket();
		$this->setTitle(array(
			Translator::trans('ticket.inprogress')
		));
		$this->addBodyClass('ticket-inprogress');
		$this->setNavigation();
	}
	private function setNavigation(){
		Navigation::active("ticketing/list");
	}
}
