<?php
namespace packages\ticketing\Logs\Tickets;
use \packages\base\View;
use \packages\userpanel\Logs;
class Add extends Logs{
	public function getColor():string{
		return "circle-green";
	}
	public function getIcon():string{
		return "fa fa-ticket";
	}
	public function buildFrontend(View $view){}
}
