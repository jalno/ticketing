<?php
namespace themes\clipone\views\ticketing\settings\department;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;

use \packages\ticketing\views\settings\department\delete as departmentDelete;

use \packages\userpanel;

use \themes\clipone\views\listTrait;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;

use \packages\ticketing\ticket;

class delete extends departmentDelete{
	use viewTrait,listTrait;
	protected $messages;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('department.delete.warning.title'),
			"#".$this->getDepartmentData()->id
		));
		$this->setShortDescription(translator::trans('department.delete.warning.title'));
		$this->setNavigation();
		navigation::active("settings/departments/list");
	}
	private function setNavigation(){
		$item = navigation::getByName("settings");
		$departments = new menuItem("departments");
		$departments->setTitle(translator::trans('departments'));
		$departments->setURL(userpanel\url('settings/departments'));
		$departments->setIcon('fa fa-university');
		$item->addItem($departments);
	}
}