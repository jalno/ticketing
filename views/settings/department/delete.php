<?php
namespace packages\ticketing\views\settings\department;

class delete extends \packages\ticketing\view{
	protected $department;
	public function setDepartmentData($department){
		$this->department = $department;
	}
	public function getDepartmentData(){
		return $this->department;
	}
}
