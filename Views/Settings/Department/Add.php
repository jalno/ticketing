<?php
namespace packages\ticketing\Views\Settings\Department;
use packages\ticketing\Views\Form;
class Add extends Form {
	public function __construct() {
		$this->setDataForm("all", "allUsers");
	}
	public function setUsers(array $users) {
		$this->setData($users, "users");
	}
	protected function getUsers(): array {
		$users = $this->getData("users");
		if (!$users) {
			$users = array();
		}
		return $users;
	}
}
