<?php
namespace packages\ticketing\Listeners;
use \packages\cronjob\Events\Tasks;
use \packages\cronjob\Task;
use \packages\cronjob\Task\Schedule;
use \packages\ticketing\Processes;
class CronJob{
	public function tasks(Tasks $event){
		$event->addTask($this->autoClose());
	}
	private function autoClose():Task{
		$task = new Task();
		$task->name = "ticketing_tickets_autoclose";
		$task->process = Processes\Tickets::class."@autoClose";
		$task->parameters = array();
		$task->schedules = array(
			new Schedule(array(
				'minute' => 0,
				'hour' => 0
			))
		);
		return $task;
	}
}
