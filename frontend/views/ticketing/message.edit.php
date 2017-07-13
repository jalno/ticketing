<?php
namespace themes\clipone\views\ticketing;
use \packages\base\translator;
use \packages\ticketing\views\message_edit as messagEdit;
use \packages\userpanel;
use \themes\clipone\viewTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;
class message_edit extends messagEdit{
	use viewTrait, formTrait;
	protected $message;
	protected $ticket;
	function __beforeLoad(){
		$this->message = $this->getMessageData();
		$this->ticket = $this->message->ticket;
		$this->setTitle(array(
			translator::trans('ticketing.edit'),
			translator::trans('ticket'),
			"#".$this->getMessageData()->id
		));
		$this->setShortDescription(translator::trans('message.edit.notice.title'));
		$this->setNavigation();
	}
	private function setNavigation(){
		$item = new menuItem("ticketing");
		$item->setTitle(translator::trans('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-user-6');
		breadcrumb::addItem($item);
		$item = new menuItem("ticketing.edit");
		$item->setTitle(translator::trans('message.edit.notice.title'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('fa fa-edit tip tooltips');
		breadcrumb::addItem($item);
		navigation::active("ticketing/list");
	}
}
