<?php
namespace themes\clipone\views\ticketing\settings\department;
use \packages\base;
use \packages\base\frontend\theme;
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
			translator::trans('settings'),
			translator::trans('departments'),
			translator::trans('department_edit')
		));
		$this->addAssets();
		navigation::active("settings/departments/list");
	}
	private function addAssets(){
		$this->addCSSFile(theme::url('assets/plugins/jQRangeSlider/css/classic-min.css'));
		$this->addJSFile(theme::url('assets/plugins/jQRangeSlider/jQAllRangeSliders-min.js'));
		$this->addJSFile(theme::url('assets/js/pages/department.js'));
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
		if(date::getCanlenderName() == 'jdate'){
			$times = $this->department->worktimes;
			usort($times, function($a, $b){
				if($a->day > $b->day){
					return 1;
				}elseif($b->day > $a->day){
					return -1;
				}
				return 0;
			});
			return array_merge(array_slice($times, 5,2),array_slice($times, 0,5));
		}
		return $this->department->worktimes;
	}
}
