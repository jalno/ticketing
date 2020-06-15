<?php
namespace packages\ticketing\controllers\settings;

use packages\base\{db, Exception, http, NotFound, translator, view\error, utility\safe, views\FormError, inputValidation, InputValidationException, db\parenthesis, response};
use packages\ticketing\{Authentication, Authorization, Controller, Department, logs, Products, Ticket, View, Views};
use packages\userpanel;
use packages\userpanel\{Log, User};

class Departments extends Controller {

	protected $authentication = true;

	public function listview() {
		Authorization::haveOrFail("settings_departments_list");
		$view = view::byName(views\settings\department\ListView::class);
		$this->response->setView($view);
		$department = new Department;
		$inputRules = array(
			"id" => array(
				"type" => "number",
				"optional" => true,
				"empty" => true,
			),
			"title" => array(
				"type" => "string",
				"optional" =>true,
				"empty" => true
			),
			"status" => array(
				"type" => "number",
				"optional" => true,
				"values" => Department::STATUSES,
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
			),
		);
		$inputs = $this->checkinputs($inputRules);
		$department = new Department;
		foreach (array("id", "title", "status") as $item) {
			if (isset($inputs[$item]) and $inputs[$item]) {
				$comparison = $inputs["comparison"];
				if (in_array($item, array("id", "status"))) {
					$comparison = "equals";
				}
				$department->where($item, $inputs[$item], $comparison);
			}
		}
		if (isset($inputs["word"]) and $inputs["word"]) {
			$parenthesis = new Parenthesis();
			foreach (array("title") as $item) {
				if (isset($inputs[$item]) or $inputs[$item]) {
					$parenthesis->where($item, $inputs["word"], $inputs["comparison"], "OR");
				}
			}
			$department->where($parenthesis);
		}
		$view->setDataForm($this->inputsvalue($inputRules));
		$department->pageLimit = $this->items_per_page;
		$departments = $department->paginate($this->page);
		$view->setDataList($departments);
		$view->setPaginate($this->page, $department->totalCount, $this->items_per_page);
		$this->response->setStatus(true);
		return $this->response;
	}
	public function delete($data) {
		Authorization::haveOrFail("settings_departments_delete");
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
		Authorization::haveOrFail("settings_departments_add");
		$this->response->setView($view = view::byName(views\settings\department\Add::class));
		$view->setUsers($this->getUsersForSelect());
		$this->response->setStatus(true);
		return $this->response;
	}
	public function store(): Response {
		Authorization::haveOrFail("settings_departments_add");
		$view = View::byName(views\settings\department\Add::class);
		$this->response->setView($view);
		$usersForSelect = $this->getUsersForSelect();
		$view->setUsers($usersForSelect);
		$products = Products::get();
		$inputs = $this->checkinputs(array(
			"title" => array(
				"type" => "string"
			),
			"status" => array(
				"type" => "number",
				"values" => Department::STATUSES,
			),
			"products" => array(
				"type" => function ($data, $rule, $input) use (&$products) {
					if (!is_string($data)) {
						throw new InputValidationException($input);
					}
					$selectedProducts = ($data ? explode(",", $data) : []);
					$existProducts = array_map(function ($item) {
						return $item->getName();
					}, $products);
					if (array_diff($selectedProducts, $existProducts)) {
						throw new InputValidationException($input);
					}
					return $selectedProducts;
				},
			),
			"mandatory_choose_product" => array(
				"type" => "bool",
				"default" => false,
				"optional" => true,
			),
			"day" => array(
				"type" => function ($data, $rule, $input) {
					if (!is_array($data)) {
						throw new InputValidationException($input);
					}
					foreach ($data as $day => $val) {
						if (!in_array($day, range(1, 7))) {
							throw new InputValidationException("day[{$day}][enable]");
						}
						if (isset($val["enable"]) and $val["enable"]) {
							if (!isset($val["worktime"]["start"]) or !in_array($val["worktime"]["start"], range(0,23))) {
								throw new InputValidationException("day[{$day}][worktime][start]");
							}
							if (!isset($val["worktime"]["end"]) or !in_array($val["worktime"]["end"], range(0,23))) {
								throw new InputValidationException("day[{$day}][worktime][end]");
							}
							if ($val["worktime"]["end"] < $val["worktime"]["start"]) {
								throw new InputValidationException("day[{$day}][worktime][end]");
							}
						} else {
							$data[$day]["worktime"]["start"] = $data[$day]["worktime"]["end"] = 0;
						}
						if (isset($val["message"]) and $val["message"]) {
							$val["message"] = Safe::string($val["message"]);
						}
					}
					return $data;
				},
			),
			"users" => array(
				"type" => function ($data, $rule, $input) use ($usersForSelect) {
					if (!is_array($data)) {
						throw new InputValidationException($input);
					}
					$users = array();
					foreach ($usersForSelect as $user) {
						$users[] = $user->id;
					}
					foreach ($data as $key => $id) {
						if (!in_array($id, $users)) {
							throw new InputValidationException("users[{$key}]");
						}
					}
					return $data;
				},
				"optional" => true
			),
		));
		$department = new Department;
		$department->title = $inputs["title"];
		$department->status = $inputs["status"];
		if (isset($inputs["users"])) {
			$department->users = array_values($inputs["users"]);
		}
		$department->save();
		$department->setMandatoryChooseProduct($inputs["mandatory_choose_product"]);
		if (isset($inputs["products"]) and $inputs["products"]) {
			$department->setProducts($inputs["products"]);
		}
		foreach (department\Worktime::getDays() as $day) {
			if (!isset($inputs["day"][$day])) {
				continue;
			}
			$input = $inputs["day"][$day];
			$work = new Department\Worktime();
			$work->day = $day;
			$work->department = $department->id;
			$work->time_start = $input["worktime"]["start"];
			$work->time_end = $input["worktime"]["end"];
			$work->message = isset($input["message"]) ? $input["message"] : null;
			$work->save();
		}
		$log = new Log();
		$log->user = Authentication::getID();
		$log->type = logs\settings\departments\Add::class;
		$log->title = t("ticketing.logs.settings.departments.add", ["department_id" => $department->id, "department_title" => $department->title]);
		$log->save();
		$this->response->setStatus(true);
		$this->response->Go(userpanel\url("settings/departments/edit/" . $department->id));
		return $this->response;
	}
	public function edit($data) {
		Authorization::haveOrFail("settings_departments_edit");
		$department = Department::byId($data["id"]);
		if (!$department) {
			throw new NotFound;
		}
		$view = View::byName(views\settings\department\Edit::class);
		$this->response->setView($view);
		$view->setDepartment($department);
		$view->setUsers($this->getUsersForSelect());
		$this->response->setStatus(true);
		return $this->response;
	}
	public function update($data): Response {
		Authorization::haveOrFail("settings_departments_edit");
		$view = View::byName(views\settings\department\edit::class);
		$this->response->setView($view);
		$department = Department::byId($data["id"]);
		if (!$department) {
			throw new NotFound;
		}
		$view->setDepartment($department);
		$usersForSelect = $this->getUsersForSelect();
		$view->setUsers($usersForSelect);
		$products = Products::get();
		$inputs = $this->checkinputs(array(
			"title" => array(
				"type" => "string",
				"optional" => true
			),
			"status" => array(
				"type" => "number",
				"optional" => true,
				"values" => Department::STATUSES,
			),
			"products" => array(
				"type" => function ($data, $rule, $input) use (&$products) {
					if (!is_string($data)) {
						throw new InputValidationException($input);
					}
					$selectedProducts = ($data ? explode(",", $data) : []);
					$existProducts = array_map(function ($item) {
						return $item->getName();
					}, $products);
					if (array_diff($selectedProducts, $existProducts)) {
						throw new InputValidationException($input);
					}
					return $selectedProducts;
				},
			),
			"mandatory_choose_product" => array(
				"type" => "bool",
				"default" => false,
				"optional" => true,
			),
			"day" => array(
				"type" => function ($data, $rule, $input) {
					if (!is_array($data)) {
						throw new InputValidationException($input);
					}
					foreach ($data as $day => $val) {
						if (!in_array($day, range(1, 7))) {
							throw new InputValidationException("day[{$day}][enable]");
						}
						if (isset($val["enable"]) and $val["enable"]) {
							if (!isset($val["worktime"]["start"]) or !in_array($val["worktime"]["start"], range(0,23))) {
								throw new InputValidationException("day[{$day}][worktime][start]");
							}
							if (!isset($val["worktime"]["end"]) or !in_array($val["worktime"]["end"], range(0,23))) {
								throw new InputValidationException("day[{$day}][worktime][end]");
							}
							if ($val["worktime"]["end"] < $val["worktime"]["start"]) {
								throw new InputValidationException("day[{$day}][worktime][end]");
							}
						} else {
							$data[$day]["worktime"]["start"] = $data[$day]["worktime"]["end"] = 0;
						}
						if (isset($val["message"]) and $val["message"]) {
							$val["message"] = Safe::string($val["message"]);
						}
					}
					return $data;
				},
			),
			"users" => array(
				"type" => function ($data, $rule, $input) use ($usersForSelect) {
					if (!is_array($data)) {
						throw new InputValidationException($input);
					}
					$users = array();
					foreach ($usersForSelect as $user) {
						$users[] = $user->id;
					}
					foreach ($data as $key => $id) {
						if (!in_array($id, $users)) {
							throw new InputValidationException("users[{$key}]");
						}
					}
					return $data;
				},
				"optional" => true
			),
		));
		$parameters = array(
			"oldData" => array(),
			"newData" => array(),
		);
		foreach (array("title", "status") as $item) {
			if (isset($inputs[$item]) and $department->$item != $inputs[$item]) {
				$parameters["oldData"][$item] = $department->$item;
				$parameters["newData"][$item] = $inputs[$item];
				$department->$item = $inputs[$item];
			}
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
		if (isset($inputs["products"])) {
			$products = $department->getProducts();
			if (array_diff($products, $inputs["products"]) or array_diff($inputs["products"], $products)) {
				$parameters["oldData"]["products"] = $products;
				$parameters["newData"]["products"] = $inputs["products"];
				$department->setProducts($inputs["products"]);
			}
		}
		if (isset($inputs["mandatory_choose_product"])) {
			if ($department->isMandatoryChooseProduct() != $inputs["mandatory_choose_product"]) {
				$department->setMandatoryChooseProduct($inputs["mandatory_choose_product"]);
			}
		}
		$days = department\Worktime::getDays();
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
			$work = new department\Worktime();
			$work->day = $day;
			$work->department = $department->id;
			$work->time_start = $item["worktime"]["start"];
			$work->time_end = $item["worktime"]["end"];
			$work->message = $item["message"];
			$work->save();
			$parameters["newData"]["worktimes"][] = $work;
		}
		$log = new Log();
		$log->user = Authentication::getID();
		$log->type = logs\settings\departments\edit::class;
		$log->title = t("ticketing.logs.settings.departments.edit", ["department_id" => $department->id, "department_title" => $department->title]);
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
		$user = new User();
		$user->where("type", $priority, "IN");
		$user->where("type", $permission, "IN");
		return $user->get();
	}
}
class ticketDependencies extends Exception {}