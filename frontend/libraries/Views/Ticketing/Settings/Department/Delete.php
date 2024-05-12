<?php
namespace themes\clipone\Views\Ticketing\Settings\Department;
use \packages\base;
use \packages\base\Frontend\Theme;
use \packages\base\Translator;

use \packages\ticketing\Views\Settings\Department\Delete as DepartmentDelete;

use \packages\userpanel;

use \themes\clipone\Views\ListTrait;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \themes\clipone\Breadcrumb;
use \themes\clipone\Navigation\MenuItem;
use themes\clipone\Views\Ticketing\HelperTrait;

use \packages\ticketing\Ticket;

class Delete extends DepartmentDelete{
	use ViewTrait,ListTrait;
	use HelperTrait;

	protected $messages;
	function __beforeLoad(){
		$this->setTitle(t("department.delete.warning.title"));
		$this->setNavigation();
		Navigation::active($this->getTicketingSettingsMenuItemName("departments"));
	}
	private function setNavigation(){
		$item = Navigation::getByName("settings");
		$departments = new MenuItem("departments");
		$departments->setTitle(Translator::trans('departments'));
		$departments->setURL(userpanel\url('settings/departments'));
		$departments->setIcon('fa fa-university');
		$item->addItem($departments);
	}
}
