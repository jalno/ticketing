<?php
namespace themes\clipone\views\ticketing;

use \packages\ticketing\views\message_edit as messagEdit;
use \packages\userpanel;
use \themes\clipone\viewTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;
use themes\clipone\views\ticketing\HelperTrait;

class message_edit extends messagEdit
{
	use viewTrait, formTrait;
	use HelperTrait;

	protected $message;
	protected $ticket;

	public function __beforeLoad()
	{
		$this->message = $this->getMessageData();
		$this->ticket = $this->message->ticket;

		$this->setTitle(array(
			t('ticketing.edit'),
			t('ticket'),
			"#".$this->getMessageData()->id
		));
		$this->setShortDescription(t('message.edit.notice.title'));
		$this->setNavigation();

		$this->initFormData();
	}

	private function setNavigation(){
		$item = new menuItem("ticketing");
		$item->setTitle(t('ticketing'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('clip-user-6');
		breadcrumb::addItem($item);
		$item = new menuItem("ticketing.edit");
		$item->setTitle(t('message.edit.notice.title'));
		$item->setURL(userpanel\url('ticketing'));
		$item->setIcon('fa fa-edit tip tooltips');
		breadcrumb::addItem($item);
		navigation::active("ticketing/list");
	}

	public function initFormData()
	{
		if (!$this->getDataForm('message_format')) {
			$this->setDataForm($this->message->format, 'message_format');
		}
		if (!$this->getDataForm('content')) {
			$this->setDataForm($this->message->text, 'content');
		}
	}
}
