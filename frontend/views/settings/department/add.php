<?php
namespace themes\clipone\views\ticketing\settings\department;

use packages\base;
use packages\ticketing\department\worktime;
use packages\ticketing\views\settings\department\Add as DepartmentAdd;
use packages\userpanel;
use packages\userpanel\Date;
use themes\clipone\{Navigation, ViewTrait};
use themes\clipone\views\{DepartmentTrait, FormTrait};

class add extends DepartmentAdd {
	use DepartmentTrait, FormTrait, ViewTrait;
	protected $days = array();
	function __beforeLoad(){
		$this->setTitle(array(
			t('settings'),
			t('departments'),
			t('department_add'),
		));
		$this->setNavigation();
		$this->setDaysValue();
		navigation::active("settings/departments/list");
		$this->addBodyClass("departments");
		$this->addBodyClass("departments-add");
	}
	private function setNavigation(){
		$item = navigation::getByName("settings");
		$departments = new navigation\menuItem("departments");
		$departments->setTitle(t('departments'));
		$departments->setURL(userpanel\url('settings/departments'));
		$departments->setIcon('fa fa-university');
		$item->addItem($departments);
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
