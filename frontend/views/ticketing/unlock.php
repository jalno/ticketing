<?php
namespace themes\clipone\views\ticketing;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;

use \packages\ticketing\views\unlock as ticketUnlock;

use \packages\userpanel;

use \themes\clipone\views\listTrait;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;

use \packages\ticketing\ticket;

class unlock extends ticketUnlock{
	use viewTrait,listTrait;
	protected $messages;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('ticket.unlock'),
			"#".$this->getTicketData()->id
		));
		$this->setShortDescription(translator::trans('ticket.unlock'));
		$this->setNavigation();
	}
	private function setNavigation(){
		$item = new menuItem("ticketing");
		$item->setTitle(translator::trans('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-user-6');
		breadcrumb::addItem($item);

		$item = new menuItem("ticketing.unlock");
		$item->setTitle(translator::trans("ticket.unlock"));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('fa fa-unlock tip');
		breadcrumb::addItem($item);

		navigation::active("ticketing/list");
	}
}
