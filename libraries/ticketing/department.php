<?php
namespace packages\ticketing;

use packages\base\db\dbObject;
use packages\userpanel\Date;
use packages\ticketing\department\Worktime;

class Department extends dbObject {
	use Paramable;

	const ACTIVE = 1;
	const DEACTIVE = 2;

	const STATUSES = array(
		Self::ACTIVE,
		Self::DEACTIVE,
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
		'worktimes' => array('hasMany', Worktime::class, 'department')
	);
	protected function isWorking() {
		$worktime = $this->currentWork();
		return($worktime->time_start <= date::format("H") and $worktime->time_end >= date::format("H"));
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
			$work = new Worktime(array(
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
