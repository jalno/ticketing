<?php
namespace themes\clipone\views\ticketing;
use \packages\ticketing\views\ticketlist as ticketListView;
use \packages\userpanel;
use \themes\clipone\navigation;
use \themes\clipone\navigation\menuItem;
use \themes\clipone\views\listTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\viewTrait;
use \packages\base\translator;

use \packages\ticketing\ticket;

class listview extends ticketListView{
	use viewTrait,listTrait,formTrait;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('ticketing'),
			translator::trans('list')
		));
		$this->setButtons();
		$this->onSourceLoad();
		navigation::active("ticketing/list");
	}
	public function setButtons(){
		$this->setButton('view', $this->canView, array(
			'title' => translator::trans('ticketing.view'),
			'icon' => 'fa fa-credit-card',
			'classes' => array('btn', 'btn-xs', 'btn-green')
		));
		$this->setButton('delete', $this->canDel, array(
			'title' => translator::trans('ticketing.delete'),
			'icon' => 'fa fa-times',
			'classes' => array('btn', 'btn-xs', 'btn-bricky')
		));
	}
	public static function onSourceLoad(){
		parent::onSourceLoad();
		if(parent::$navigation){
			$item = new menuItem("ticketing");
			$item->setTitle(translator::trans('ticketing'));
			$item->setURL(userpanel\url('ticketing'));
			$item->setIcon('clip-user-6');
			navigation::addItem($item);
		}
	}
	protected function department(){
		$choose = array(
			array(
				'title' => translator::trans("choose"),
				'value' => ''
			)
		);
		return array_merge($choose, $this->getDepartment());
	}
	protected function Priorty(){
		return array(
			array(
				'title' => translator::trans("choose"),
				"value" => ''
			),
			array(
				'title' => translator::trans("instantaneous"),
				'value' => ticket::instantaneous
			),
			array(
				'title' => translator::trans("important"),
				'value' => ticket::important
			),
			array(
				'title' => translator::trans("ordinary"),
				'value' => ticket::ordinary
			)
		);
	}
	protected function Status(){
		return array(
			array(
				'title' => translator::trans("choose"),
				"value" => ''
			),
			array(
				'title' => translator::trans("unread"),
				'value' => ticket::unread
			),
			array(
				'title' => translator::trans("read"),
				'value' => ticket::read
			),
			array(
				'title' => translator::trans("answered"),
				'value' => ticket::answered
			),
			array(
				'title' => translator::trans("in_progress"),
				'value' => ticket::in_progress
			),
			array(
				'title' => translator::trans("closed"),
				'value' => ticket::closed
			)
		);
	}
}
