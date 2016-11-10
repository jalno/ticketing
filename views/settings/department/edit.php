<?php
namespace packages\ticketing\views\settings\department;

class edit extends \packages\ticketing\views\form{
	protected $department;
	public function setDepartmentData($department){
		$this->department = $department;
	}
	public function getDepartmentData(){
		return $this->department;
	}
}
