<?php
namespace packages\ticketing;

use packages\base\DB\DBObject;
use packages\userpanel\Date;
use packages\ticketing\Department\WorkTime;

class Department extends DBObject {
	use Paramable;

	const ACTIVE = 1;
	const DEACTIVE = 2;

	const STATUSES = array(
		self::ACTIVE,
		self::DEACTIVE,
	);

	protected $dbTable = "ticketing_departments";
	protected $primaryKey = "id";
	protected $dbFields = array(
		'title' => array('type' => 'text', 'required' => true),
		'users' => array('type' => 'text'),
		'status' => array('type' => 'int', 'required' => true),
    );
	protected $jsonFields = ["users"];
	protected $relations = array(
		'worktimes' => array('hasMany', WorkTime::class, 'department')
	);
	public function setProducts(array $products): void {
		$this->setParam("products", $products);
	}
	public function getProducts(): array { // if products is an empty array, all products are accpetable for this department
		return $this->param("products") ?? [];
	}
	public function setMandatoryChooseProduct(bool $isMandatory): void {
		$this->setParam("mandatory_choose_product", $isMandatory);
	}
	public function isMandatoryChooseProduct(): bool {
		return !!$this->param("mandatory_choose_product");
	}
	protected function isWorking() {
		$worktime = $this->currentWork();
		if (!$worktime) {
			return false;
		}
		return($worktime->time_start <= Date::format("H") and $worktime->time_end >= Date::format("H"));
	}
	protected function currentWork() {
		foreach ($this->worktimes as $worktime) {
			if ($worktime->day == Date::format("N")) {
				return $worktime;
			}
		}
	}
	protected $addworktimes = array();
	public function addWorkTimes() {
		$this->data['worktimes'] = array();
		for ($x = 1; $x != 8; $x++) {
			$work = new WorkTime(array(
				'department' => $this->id,
				'day' => $x
			));
			$work->save();
			$this->data['worktimes'][] = $work;
		}
	}
	public function save($data = null){
		$isNew = $this->isNew;
		if(($return = parent::save($data))){
			if($isNew){
				$this->addWorkTimes();
			}

		}
		return $return;
	}
}
