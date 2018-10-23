<?php
namespace themes\clipone\views\ticketing\settings\department;
use \packages\base\translator;
use \packages\ticketing\views\settings\department\edit as departmentEdit;
use \packages\userpanel;
use \packages\userpanel\date;
use \themes\clipone\views\formTrait;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;
use \packages\ticketing\department\worktime;
class edit extends departmentEdit{
	use viewTrait,formTrait;
	protected $department;
	function __beforeLoad(){
		$this->department = $this->getDepartment();
		$this->setTitle(array(
			translator::trans("settings"),
			translator::trans("departments"),
			translator::trans("department_edit")
		));
		navigation::active("settings/departments/list");
	}
	protected function getTranslatDays($day){
		switch($day){
			case(worktime::saturday):
				return translator::trans("ticketing.departments.worktime.saturday");
			case(worktime::sunday):
				return translator::trans("ticketing.departments.worktime.sunday");
			case(worktime::monday):
				return translator::trans("ticketing.departments.worktime.monday");
			case(worktime::tuesday):
				return translator::trans("ticketing.departments.worktime.tuesday");
			case(worktime::wednesday):
				return translator::trans("ticketing.departments.worktime.wednesday");
			case(worktime::thursday):
				return translator::trans("ticketing.departments.worktime.thursday");
			case(worktime::friday):
				return translator::trans("ticketing.departments.worktime.friday");
		}
	}
	protected function sortedDays(){
		if (date::getCanlenderName() == "jdate") {
			return array(
				array("day" => worktime::saturday),
				array("day" => worktime::sunday),
				array("day" => worktime::monday),
				array("day" => worktime::tuesday),
				array("day" => worktime::wednesday),
				array("day" => worktime::thursday),
				array("day" => worktime::friday),
			);
		}
		return array(
			array("day" => worktime::monday),
			array("day" => worktime::tuesday),
			array("day" => worktime::wednesday),
			array("day" => worktime::thursday),
			array("day" => worktime::friday),
			array("day" => worktime::saturday),
			array("day" => worktime::sunday),
		);
	}
}
