<?php
namespace themes\clipone\views\ticketing;
use \packages\base\translator;
use \packages\ticketing\views\close as ticketClose;
use \packages\userpanel;
use \themes\clipone\views\formTrait;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \packages\ticketing\ticket;
class close extends ticketClose{
	use viewTrait, formTrait;
	protected $ticket;
	function __beforeLoad(){
		$this->ticket = $this->getTicket();
		$this->setTitle(array(
			translator::trans('ticket.close')
		));
		$this->addBodyClass('ticket-close');
		$this->setNavigation();
	}
	private function setNavigation(){
		navigation::active("ticketing/list");
	}
}
