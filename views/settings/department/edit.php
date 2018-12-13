<?php
namespace packages\ticketing\views\settings\department;
use packages\base\db\dbObject;
use packages\ticketing\{views\form, department};
class edit extends form {
	public function setDepartment(department $department) {
		$this->setData($department, "department");

		$this->setDataForm($department->title,"title");
		foreach ($department->worktimes as $work) {
			$this->setDataForm(($work->time_start or $work->time_end), "day[{$work->day}][enable]");
			$this->setDataForm($work->time_start, "day[{$work->day}][worktime][start]");
			$this->setDataForm($work->time_end, "day[{$work->day}][worktime][end]");
			$this->setDataForm($work->message, "day[{$work->day}][message]");
		}
		if ($department->users) {
			foreach ($department->users as $user) {
				$this->setDataForm($user, "users[{$user}]");
			}
		} else {
			$this->setDataForm("all", "allUsers");
		}
	}
	public function getDepartment() {
		return $this->getData("department");
	}
	public function export(): array {
		$department = $this->getDepartment();
		$data = array(
			"department" => $department->toArray(),
		);
		if ($currentWork = $department->currentWork()) {
			$data["department"]["currentWork"] =  $currentWork->toArray();
		}
		return array(
			"data" => $data,
			"users" => dbObject::objectToArray($this->getUsers()),
		);
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
