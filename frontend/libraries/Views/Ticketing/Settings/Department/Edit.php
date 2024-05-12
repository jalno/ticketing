<?php
namespace themes\clipone\Views\Ticketing\Settings\Department;

use packages\base;
use packages\ticketing\Department\WorkTime;
use packages\ticketing\Views\Settings\Department\Edit as DepartmentEdit;
use packages\userpanel;
use packages\userpanel\Date;
use themes\clipone\Views\{DepartmentTrait, FormTrait};
use themes\clipone\{Breadcrumb, Navigation\MenuItem, Navigation, ViewTrait};
use themes\clipone\Views\Ticketing\HelperTrait;

class Edit extends DepartmentEdit {
	use DepartmentTrait, FormTrait, ViewTrait;
	use HelperTrait;

	protected $department;
	function __beforeLoad(){
		$this->department = $this->getDepartment();
		$this->setTitle(t("department_edit"));
		Navigation::active($this->getTicketingSettingsMenuItemName("departments"));
		$this->addBodyClass("departments");
		$this->addBodyClass("departments-add");
	}
	protected function sortedDays() {
		$days = array();
		$firstDay = Date::getFirstDayOfWeek();
		for ($i = $firstDay; $i < $firstDay + 7; $i++) {
			$days[] = array(
				'day' => ($i % 7),
			);
		}
		return $days;
	}
}
