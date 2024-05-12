<?php
namespace themes\clipone\Views\Ticketing;
use \packages\base;
use \packages\base\Frontend\Theme;
use \packages\base\Translator;

use \packages\ticketing\Views\Delete as TicketDelete;

use \packages\userpanel;

use \themes\clipone\Views\ListTrait;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \themes\clipone\Breadcrumb;
use \themes\clipone\Navigation\MenuItem;

use \packages\ticketing\Ticket;

class Delete extends TicketDelete{
	use ViewTrait,ListTrait;
	protected $messages;
	function __beforeLoad(){
		$this->setTitle(array(
			Translator::trans('ticket.delete.warning.title'),
			"#".$this->getTicketData()->id
		));
		$this->setShortDescription(Translator::trans('ticket.delete.warning.title'));
		$this->setNavigation();
	}
	private function setNavigation(){
		$item = new MenuItem("ticketing");
		$item->setTitle(Translator::trans('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-user-6');
		Breadcrumb::addItem($item);

		$item = new MenuItem("ticketing.unlock");
		$item->setTitle(Translator::trans("ticket.delete.warning.title"));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('fa fa-trash-o tip');
		Breadcrumb::addItem($item);

		Navigation::active("ticketing/list");
	}
}
