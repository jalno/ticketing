<?php
namespace packages\ticketing\views\settings\department;
use \packages\ticketing\department;
use \packages\ticketing\views\form;
class edit extends form {
	public function setDepartment(department $department){
		$this->setData($department, "department");

		$this->setDataForm($department->title,"title");
		foreach($department->worktimes as $work){
			$this->setDataForm(($work->time_start or $work->time_end), "day[{$work->day}][enable]");
			$this->setDataForm($work->time_start, "day[{$work->day}][worktime][start]");
			$this->setDataForm($work->time_end, "day[{$work->day}][worktime][end]");
			$this->setDataForm($work->message, "day[{$work->day}][message]");
		}
	}
	public function getDepartment(){
		return $this->getData("department");
	}
	public function export(){
		$department = $this->getDepartment();
		$data = array(
			"department" => $department->toArray(),
		);
		if ($currentWork = $department->currentWork()) {
			$data["department"]["currentWork"] =  $currentWork->toArray();
		}
		return array(
			"data" => $data
		);
	}
}
