<?php
namespace packages\ticketing\listeners;
use \packages\cronjob\events\tasks;
use \packages\cronjob\task;
use \packages\cronjob\task\schedule;
use \packages\ticketing\processes;
class cronjob{
	public function tasks(tasks $event){
		$event->addTask($this->autoClose());
	}
	private function autoClose():task{
		$task = new task();
		$task->name = "ticketing_tickets_autoclose";
		$task->process = processes\tickets::class."@autoClose";
		$task->parameters = array();
		$task->schedules = array(
			new schedule(array(
				'minute' => 0
			)),
		);
		return $task;
	}
}
