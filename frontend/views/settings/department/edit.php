<?php
namespace themes\clipone\views\ticketing\settings\department;

use packages\base;
use packages\ticketing\department\Worktime;
use packages\ticketing\views\settings\department\Edit as DepartmentEdit;
use packages\userpanel;
use packages\userpanel\Date;
use themes\clipone\views\{DepartmentTrait, FormTrait};
use themes\clipone\{Breadcrumb, navigation\MenuItem, Navigation, ViewTrait};

class Edit extends DepartmentEdit {
	use DepartmentTrait, FormTrait, ViewTrait;
	protected $department;
	function __beforeLoad(){
		$this->department = $this->getDepartment();
		$this->setTitle(t("department_edit"));
		Navigation::active("settings/departments/list");
		$this->addBodyClass("departments");
		$this->addBodyClass("departments-add");
	}
	protected function sortedDays(){
		if (Date::getCanlenderName() == "jdate") {
			return array(
				array("day" => Worktime::saturday),
				array("day" => Worktime::sunday),
				array("day" => Worktime::monday),
				array("day" => Worktime::tuesday),
				array("day" => Worktime::wednesday),
				array("day" => Worktime::thursday),
				array("day" => Worktime::friday),
			);
		}
		return array(
			array("day" => Worktime::monday),
			array("day" => Worktime::tuesday),
			array("day" => Worktime::wednesday),
			array("day" => Worktime::thursday),
			array("day" => Worktime::friday),
			array("day" => Worktime::saturday),
			array("day" => Worktime::sunday),
		);
	}
}
