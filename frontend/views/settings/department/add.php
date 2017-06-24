<?php
namespace themes\clipone\views\ticketing\settings\department;
use \packages\base\translator;
use \packages\ticketing\views\settings\department\add as departmentAdd;
use \packages\userpanel;
use \packages\userpanel\date;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\views\formTrait;
use \packages\ticketing\department\worktime;
class add extends departmentAdd{
	use viewTrait, formTrait;
	protected $days = array();
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('settings'),
			translator::trans('departments'),
			translator::trans('department_add'),
		));
		$this->setNavigation();
		$this->setDaysValue();
		navigation::active("settings/departments/list");
	}
	private function setNavigation(){
		$item = navigation::getByName("settings");
		$departments = new navigation\menuItem("departments");
		$departments->setTitle(translator::trans('departments'));
		$departments->setURL(userpanel\url('settings/departments'));
		$departments->setIcon('fa fa-university');
		$item->addItem($departments);
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
	private function setDaysValue(){
		for($i = 1; $i <= 7; $i++){
			$this->days[] = array(
				'day' => $i
			);
		}
	}
	protected function sortedDays(){
		if(date::getCanlenderName() == 'jdate'){
			$times = $this->days;
			usort($times, function($a, $b){
				if($a['day'] > $b['day']){
					return 1;
				}elseif($b['day'] > $a['day']){
					return -1;
				}
				return 0;
			});
			return array_merge(array_slice($times, 5,2),array_slice($times, 0,5));
		}
		return $this->days;
	}
}
