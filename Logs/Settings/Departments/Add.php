<?php
namespace packages\ticketing\Logs\Settings\Departments;
use \packages\base\View;
use \packages\userpanel\Logs;
class Add extends Logs{
	public function getColor():string{
		return "circle-green";
	}
	public function getIcon():string{
		return "fa fa-bank";
	}
	public function buildFrontend(View $view){}
}
