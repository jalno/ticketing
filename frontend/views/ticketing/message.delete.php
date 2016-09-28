<?php
namespace themes\clipone\views\ticketing;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;

use \packages\ticketing\views\message_delete as messageDelete;

use \packages\userpanel;

use \themes\clipone\viewTrait;
use \themes\clipone\views\listTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;


class message_delete extends messageDelete{
	use viewTrait,listTrait;
	protected $networks = array();
	protected $lastlogin = 0;
	protected $logs;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('ticketing.delete'),
			translator::trans('ticket'),
			"#".$this->getMessageData()->id
		));
		$this->setShortDescription(translator::trans('message.delete.warning.title'));
		$this->setNavigation();
	}
	private function setNavigation(){
		$item = new menuItem("ticketing");
		$item->setTitle(translator::trans('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-user-6');
		breadcrumb::addItem($item);

		$item = new menuItem("ticketing.delete");
		$item->setTitle(translator::trans('message.delete.warning.title'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('fa fa-trash-o');
		breadcrumb::addItem($item);


		navigation::active("ticketing/list");
	}
}
