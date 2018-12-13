<?php
namespace packages\ticketing\views\settings\department;
use packages\ticketing\views\form;
class add extends form {
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
