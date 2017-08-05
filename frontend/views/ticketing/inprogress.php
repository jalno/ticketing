<?php
namespace themes\clipone\views\ticketing;
use \packages\base\translator;
use \packages\ticketing\views\inprogress as ticketInProgress;
use \packages\userpanel;
use \themes\clipone\views\formTrait;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \packages\ticketing\ticket;
class inprogress extends ticketInProgress{
	use viewTrait, formTrait;
	protected $ticket;
	function __beforeLoad(){
		$this->ticket = $this->getTicket();
		$this->setTitle(array(
			translator::trans('ticket.inprogress')
		));
		$this->addBodyClass('ticket-inprogress');
		$this->setNavigation();
	}
	private function setNavigation(){
		navigation::active("ticketing/list");
	}
}
