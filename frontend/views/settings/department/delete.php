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
use themes\clipone\views\ticketing\HelperTrait;

use \packages\ticketing\ticket;

class delete extends departmentDelete{
	use viewTrait,listTrait;
	use HelperTrait;

	protected $messages;
	function __beforeLoad(){
		$this->setTitle(t("department.delete.warning.title"));
		$this->setNavigation();
		Navigation::active($this->getTicketingSettingsMenuItemName("departments"));
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
