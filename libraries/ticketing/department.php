<?php
namespace packages\ticketing;
use \packages\base\db\dbObject;
use \packages\userpanel\date;
use \packages\ticketing\department\worktime;
class department extends dbObject{
	protected $dbTable = "ticketing_departments";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'title' => array('type' => 'text', 'required' => true)
    );
	protected $relations = array(
		'worktimes' => array('hasMany', 'packages\\ticketing\\department\\worktime', 'department')
	);
	protected function isWorking(){
		$worktime = $this->currentWork();
		return($worktime->time_start <= date::format("H") and $worktime->time_end >= date::format("H"));
	}
	protected function currentWork(){
		foreach($this->worktimes as $worktime){
			if($worktime->day == date::format("N")){
				return $worktime;
			}
		}
	}
	protected $addworktimes = array();
	public function addWorkTimes(){
		$this->data['worktimes'] = array();
		for($x = 1;$x!=8;$x++){
			$work = new worktime(array(
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
