<?php
namespace packages\ticketing\Logs\Tickets;
use \packages\base\View;
use \packages\userpanel\Logs;
class Lock extends Logs{
	public function getColor():string{
		return "";
	}
	public function getIcon():string{
		return "fa fa-lock";
	}
	public function buildFrontend(View $view){}
}
