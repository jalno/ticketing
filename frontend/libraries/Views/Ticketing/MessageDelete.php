<?php
namespace themes\clipone\Views\Ticketing;
use \packages\base;
use \packages\base\Frontend\Theme;
use \packages\base\Translator;

use \packages\userpanel;

use \themes\clipone\ViewTrait;
use \themes\clipone\Views\ListTrait;
use \themes\clipone\Navigation;
use \themes\clipone\Breadcrumb;
use \themes\clipone\Navigation\MenuItem;


class MessageDelete extends \packages\ticketing\Views\MessageDelete {
	use ViewTrait,ListTrait;
	protected $networks = array();
	protected $lastlogin = 0;
	protected $logs;
	function __beforeLoad(){
		$this->setTitle(array(
			Translator::trans('ticketing.delete'),
			Translator::trans('ticket'),
			"#".$this->getMessageData()->id
		));
		$this->setShortDescription(Translator::trans('message.delete.warning.title'));
		$this->setNavigation();
	}
	private function setNavigation(){
		$item = new MenuItem("ticketing");
		$item->setTitle(Translator::trans('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-user-6');
		Breadcrumb::addItem($item);

		$item = new MenuItem("ticketing.delete");
		$item->setTitle(Translator::trans('message.delete.warning.title'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('fa fa-trash-o');
		Breadcrumb::addItem($item);


		Navigation::active("ticketing/list");
	}
}
