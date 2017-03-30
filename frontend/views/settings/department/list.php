<?php
namespace themes\clipone\views\ticketing\settings\department;
use \packages\ticketing\views\settings\department\listview as departmentList;
use \packages\userpanel;
use \themes\clipone\navigation;
use \themes\clipone\navigation\menuItem;
use \themes\clipone\views\listTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\viewTrait;
use \packages\base\translator;

use \packages\ticketing\ticket;

class listview extends departmentList{
	use viewTrait,listTrait,formTrait;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('settings'),
			translator::trans('departments'),
			translator::trans('list')
		));
		$this->setButtons();
		$this->onSourceLoad();
		navigation::active("settings/departments/list");
	}
	public function setButtons(){
		$this->setButton('edit', $this->canEdit, array(
			'title' => translator::trans('department.edit'),
			'icon' => 'fa fa-edit',
			'classes' => array('btn', 'btn-xs', 'btn-warning')
		));
		$this->setButton('delete', $this->canDel, array(
			'title' => translator::trans('department.delete'),
			'icon' => 'fa fa-times',
			'classes' => array('btn', 'btn-xs', 'btn-bricky')
		));
	}

	public function getComparisonsForSelect(){
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
	public static function onSourceLoad(){
		parent::onSourceLoad();
		if(parent::$navigation){
			if($item = navigation::getByName("settings")){
				$departments = new menuItem("departments");
				$departments->setTitle(translator::trans('departments'));
				$departments->setURL(userpanel\url('settings/departments'));
				$departments->setIcon('fa fa-university');
				$item->addItem($departments);
			}
		}
	}
}
