<?php
namespace packages\ticketing\controllers\settings;
use packages\base\{db, http, NotFound, translator, view\error, utility\safe, views\FormError, inputValidation, db\parenthesis, response};
use packages\userpanel;
use packages\userpanel\{user, date, log};
use packages\ticketing\{controller, authorization, authentication, ticket, view, department, logs, views};
class departments extends controller{
	protected $authentication = true;
	public function listview() {
		authorization::haveOrFail("department_list");
		$this->response->setView($view = view::byName(views\settings\department\listview::class));
		$department = new department;
		$inputsRules = array(
			"id" => array(
				"type" => "number",
				"optional" => true,
				"empty" => true
			),
			"title" => array(
				"type" => "string",
				"optional" =>true,
				"empty" => true
			),
			"word" => array(
				"type" => "string",
				"optional" => true,
				"empty" => true
			),
			"comparison" => array(
				"values" => array("equals", "startswith", "contains"),
				"default" => "contains",
				"optional" => true
			)
		);
		$inputs = $this->checkinputs($inputsRules);
		foreach(array("id", "title") as $item) {
			if (isset($inputs[$item]) and $inputs[$item]) {
				$comparison = $inputs["comparison"];
				if (in_array($item, array("id", "status"))) {
					$comparison = "equals";
				}
				$department->where($item, $inputs[$item], $comparison);
			}
		}
		if (isset($inputs["word"]) and $inputs["word"]) {
			$parenthesis = new parenthesis();
			foreach(array("title") as $item) {
				if (!isset($inputs[$item]) or !$inputs[$item]) {
					$parenthesis->where($item,$inputs["word"], $inputs["comparison"], "OR");
				}
			}
			$department->where($parenthesis);
		}
		$view->setDataForm($this->inputsvalue($inputs));
		$department->pageLimit = $this->items_per_page;
		$departments = $department->paginate($this->page);
		$view->setDataList($departments);
		$view->setPaginate($this->page, $department->totalCount, $this->items_per_page);
		$this->response->setStatus(true);
		return $this->response;
	}
	public function delete($data) {
		authorization::haveOrFail("department_delete");
		$department = department::byId($data["id"]);
		if (!$department) {
			throw new NotFound;
		}
		$view = view::byName(views\settings\department\delete::class);
		$view->setDepartmentData($department);
		$this->response->setStatus(false);
		if (http::is_post()) {
			try{
				$ticket = new ticket();
				$ticket->where("department", $department->id);
				if ($ticket->has()) {
					throw new ticketDependencies();
				}

				$log = new log();
				$log->user = authentication::getID();
				$log->type = logs\settings\departments\delete::class;
				$log->title = translator::trans("ticketing.logs.settings.departments.delete", ["department_id" => $department->id, "department_title" => $department->title]);
				$log->parameters = ["department" => $department];
				$log->save();

				$department->delete();
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url("settings/departments"));
			}catch(inputValidation $error) {
				$view->setFormError(FormError::fromException($error));
			}catch(ticketDependencies $e) {
				$error = new error();
				$error->setCode("ticketDependencies");
				$error->setMessage(translator::trans("error.ticketDependencies", ["ticket_search_link" => userpanel\url("ticketing", ["department"=>$department->id])]));
				$view->addError($error);
			}
		} else {
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function add() {
		authorization::haveOrFail("department_add");
		$this->response->setView($view = view::byName(views\settings\department\add::class));
		$view->setUsers($usersForSelect = $this->getUsersForSelect());
		$this->response->setStatus(true);
		return $this->response;
	}
	public function store(): response {
		authorization::haveOrFail("department_add");
		$this->response->setView($view = view::byName(views\settings\department\add::class));
		$inputsRules = array(
			"title" => array(
				"type" => "string"
			),
			"day" => array(),
			"users" => array(
				"optional" => true
			),
		);
		$inputs = $this->checkinputs($inputsRules);
		$view->setUsers($usersForSelect = $this->getUsersForSelect());
		$this->response->setStatus(false);
		if (!is_array($inputs["day"])) {
			throw new inputValidation("day");
		}
		if (isset($inputs["users"])) {
			if ($inputs["users"]) {
				if (!is_array($inputs["users"])) {
					throw new inputValidation("users");
				}
			} else {
				unset($inputs["users"]);
			}
		}
		foreach ($inputs["day"] as $day => $val) {
			if (!in_array($day, range(1, 7))) {
				throw new inputValidation("day[{$day}][enable]");
			}
			if (isset($val["enable"]) and $val["enable"]) {
				if (!isset($val["worktime"]["start"]) or !in_array($val["worktime"]["start"], range(0,23))) {
					throw new inputValidation("day[{$day}][worktime][start]");
				}
				if (!isset($val["worktime"]["end"]) or !in_array($val["worktime"]["end"], range(0,23))) {
					throw new inputValidation("day[{$day}][worktime][end]");
				}
				if ($val["worktime"]["end"] < $val["worktime"]["start"]) {
					throw new inputValidation("day[{$day}][worktime][end]");
				}
			} else {
				$inputs["day"][$day]["worktime"]["start"] = $inputs["day"][$day]["worktime"]["end"] = 0;
			}
			if (isset($val["message"]) and $val["message"]) {
				$val["message"] = safe::string($val["message"]);
			}
		}
		if (isset($inputs["users"])) {
			$users = array();
			foreach ($usersForSelect as $user) {
				$users[] = $user->id;
			}
			foreach ($inputs["users"] as $key => $id) {
				if (!in_array($id, $users)) {
					throw new inputValidation("users[{$key}]");
				}
			}
		}
		$department = new department;
		$department->title = $inputs["title"];
		if (isset($inputs["users"])) {
			$department->users = array_values($inputs["users"]);
		}
		$department->save();
		foreach (department\worktime::getDays() as $day) {
			if (!isset($inputs["day"][$day])) {
				continue;
			}
			$input = $inputs["day"][$day];
			$work = new department\worktime();
			$work->day = $day;
			$work->department = $department->id;
			$work->time_start = $input["worktime"]["start"];
			$work->time_end = $input["worktime"]["end"];
			$work->message = isset($input["message"]) ? $input["message"] : null;
			$work->save();
		}
		$log = new log();
		$log->user = authentication::getID();
		$log->type = logs\settings\departments\add::class;
		$log->title = translator::trans("ticketing.logs.settings.departments.add", ["department_id" => $department->id, "department_title" => $department->title]);
		$log->save();
		$this->response->setStatus(true);
		$this->response->Go(userpanel\url("settings/departments/edit/".$department->id));
		return $this->response;
	}
	public function edit($data) {
		authorization::haveOrFail("department_edit");
		$this->response->setView($view = view::byName(views\settings\department\edit::class));
		$department = department::byId($data["id"]);
		if (!$department) {
			throw new NotFound;
		}
		$view->setDepartment($department);
		$view->setUsers($usersForSelect = $this->getUsersForSelect());
		$this->response->setStatus(true);
		return $this->response;
	}
	public function update($data): response {
		authorization::haveOrFail("department_edit");
		$this->response->setView($view = view::byName(views\settings\department\edit::class));
		$department = department::byId($data["id"]);
		if (!$department) {
			throw new NotFound;
		}
		$view->setDepartment($department);
		$view->setUsers($usersForSelect = $this->getUsersForSelect());
		$inputsRules = array(
			"title" => array(
				"type" => "string",
				"optional" => true
			),
			"day" => array(),
			"users" => array(
				"optional" => true
			),
		);
		$inputs = $this->checkinputs($inputsRules);
		$this->response->setStatus(false);
		if (isset($inputs["users"])) {
			if ($inputs["users"]) {
				if (!is_array($inputs["users"])) {
					throw new inputValidation("users");
				}
			} else {
				unset($inputs["users"]);
			}
		}
		if (is_array($inputs["day"])) {
			foreach($inputs["day"] as $day => $val) {
				if (!in_array($day, range(1,7))) {
					throw new inputValidation("day[{$day}][enable]");
				}
				if (isset($val["enable"]) and $val["enable"]) {
					if (!isset($val["worktime"]["start"]) or !in_array($val["worktime"]["start"], range(0,23))) {
						throw new inputValidation("day[{$day}][worktime][start]");
					}
					if (!isset($val["worktime"]["end"]) or !in_array($val["worktime"]["end"], range(0,23))) {
						throw new inputValidation("day[{$day}][worktime][end]");
					}
					if ($val["worktime"]["end"] < $val["worktime"]["start"]) {
						throw new inputValidation("day[{$day}][worktime][end]");
					}
				} else {
					$inputs["day"][$day]["worktime"]["start"] = $inputs["day"][$day]["worktime"]["end"] = 0;
				}
				if (isset($val["message"]) and $val["message"]) {
					$val["message"] = safe::string($val["message"]);
				}
			}
		} else {
			throw new inputValidation("day");
		}
		if (isset($inputs["users"])) {
			$users = array();
			foreach ($usersForSelect as $user) {
				$users[] = $user->id;
			}
			foreach ($inputs["users"] as $key => $id) {
				if (!in_array($id, $users)) {
					throw new inputValidation("users[{$key}]");
				}
			}
		}
		$parameters = array(
			"oldData" => array(),
			"newData" => array(),
		);
		if (isset($inputs["title"]) and $department->title != $inputs["title"]) {
			$parameters["oldData"]["title"] = $department->title;
			$parameters["newData"]["title"] = $inputs["title"];
			$department->title = $inputs["title"];
		}
		if (isset($inputs["users"])) {
			if ($department->users) {
				$users = array_values($inputs["users"]);
				foreach ($department->users as $user) {
					if (($key = array_search($user, $users) !== false)) {
						unset($users[$key]);
					} else {
						$parameters["oldData"]["users"][] = $user;
					}
				}
				if ($users) {
					$parameters["newData"]["users"][] = $users;
				}
			}
			$department->users = array_values($inputs["users"]);
		} else if ($department->users) {
			$parameters["oldData"]["users"] = $department->users;
			$department->users = null;
		}
		$department->save();
		$days = department\worktime::getDays();
		foreach ($days as $key => $day) {
			$input = null;
			$work = new department\worktime();
			$work->where("day", $day);
			$work->where("department", $department->id);
			if (!$work = $work->getOne()) {
				continue;
			}
			if (isset($inputs["day"][$day])) {
				$input = $inputs["day"][$day];
				if (
					$work->time_start != $input["worktime"]["start"] or
					$work->time_end != $input["worktime"]["end"] or
					$work->message != $input["message"]
				) {
					$parameters["oldData"]["worktimes"][] = $work;
				}

				$work->time_start = $input["worktime"]["start"];
				$work->time_end = $input["worktime"]["end"];
				$work->message = $input["message"];
				$work->save();
				unset($inputs["day"][$day]);
			} else {
				$parameters["oldData"]["worktimes"][] = $work;
				$work->delete();
			}
		}
		foreach ($inputs["day"] as $day => $item) {
			$work = new department\worktime();
			$work->day = $day;
			$work->department = $department->id;
			$work->time_start = $item["worktime"]["start"];
			$work->time_end = $item["worktime"]["end"];
			$work->message = $item["message"];
			$work->save();
			$parameters["newData"]["worktimes"][] = $work;
		}
		$log = new log();
		$log->user = authentication::getID();
		$log->type = logs\settings\departments\edit::class;
		$log->title = translator::trans("ticketing.logs.settings.departments.edit", ["department_id" => $department->id, "department_title" => $department->title]);
		$log->parameters = $parameters;
		$log->save();
		$this->response->setStatus(true);
		return $this->response;
	}
	protected function getUsersForSelect(): array {
		$priority = db::subQuery();
		$priority->setQueryOption("DISTINCT");
		$priority->get("userpanel_usertypes_priorities", null, "parent");
		$permission = db::subQuery();
		$permission->where("name", "ticketing_view");
		$permission->get("userpanel_usertypes_permissions", null, "type");
		$user = new user();
		$user->where("type", $priority, "IN");
		$user->where("type", $permission, "IN");
		return $user->get();
	}
}
class ticketDependencies extends \Exception{}