<?php
namespace packages\ticketing\controllers;

use packages\base\{db, InputValidationException, db\Parenthesis, Response, NotFound, View};
use packages\ticketing\{Controller, Authorization, Department, Ticket};
use packages\userpanel\{Authentication, User};

class Userpanel extends controller {
	protected $authentication = true;
	public function operators($data): response {
		authorization::haveOrFail("edit");
		$department = department::byId($data["department"]);
		if (! $department) {
			throw new NotFound();
		}
		$inputs = $this->checkinputs(array(
			"word" => array(),
		));
		$this->response->setStatus(true);
		$users = $department->users;
		$priority = db::subQuery();
		$priority->setQueryOption("DISTINCT");
		$priority->get("userpanel_usertypes_priorities", null, "parent");
		$permission = db::subQuery();
		$permission->where("name", "ticketing_view");
		$permission->get("userpanel_usertypes_permissions", null, "type");
		$model = new user();
		$model->where("type", $priority, "IN");
		$model->where("type", $permission, "IN");
		if ($users) {
			$model->where("id", $users, "IN");
		}
		$parenthesis = new parenthesis();
		foreach (array("name", "lastname", "email", "cellphone") as $item) {
			$parenthesis->orWhere($item, $inputs["word"], "contains");
		}
		$parenthesis->orWhere("CONCAT(`name`, ' ', `lastname`)", $inputs["word"], "contains");
		$model->where($parenthesis);
		$this->response->setData($model->arrayBuilder()->get(), "items");
		return $this->response;
	}
}
