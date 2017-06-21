<?php
namespace themes\clipone\views\ticketing;
use \packages\userpanel;
use \packages\base\translator;
use \packages\base\view\error;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\views\listTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\navigation\menuItem;
use \packages\ticketing\ticket;
use \packages\ticketing\views\ticketlist as ticketListView;
class listview extends ticketListView{
	use viewTrait, listTrait, formTrait;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('ticketing'),
			translator::trans('list')
		));
		$this->setButtons();
		$this->onSourceLoad();
		navigation::active("ticketing/list");
		if(empty($this->getTickets())){
			$this->addNotFoundError();
		}
	}
	private function addNotFoundError(){
		$error = new error();
		$error->setType(error::NOTICE);
		$error->setCode('ticketing.ticket.notfound');
		$error->setData([
			[
				'type' => 'btn-teal',
				'txt' => translator::trans('ticketing.add'),
				'link' => userpanel\url('ticketing/new')
			]
		], 'btns');
		$this->addError($error);
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
			$item->setPriority(280);
			navigation::addItem($item);
		}
	}
	protected function getDepartmentsForSelect(){
		$departments = array();
		$departments[0] = array(
			'title' => translator::trans("choose"),
			'value' => ''
		);
		foreach($this->getDepartment() as $department){
			$departments[] = array(
				'title' => $department->title,
				'value' => $department->id
			);
		}
		return $departments;
	}
	protected function getPriortyForSelect(){
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
	protected function getStatusForSelect(){
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
	protected function getComparisonsForSelect(){
		return array(
			array(
				'title' => translator::trans('search.comparison.contains'),
				'value' => 'contains'
			),
			array(
				'title' => translator::trans('search.comparison.equals'),
				'value' => 'equals'
			),
			array(
				'title' => translator::trans('search.comparison.startswith'),
				'value' => 'startswith'
			)
		);
	}
}
