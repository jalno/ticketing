<?php
namespace packages\ticketing\logs\settings\departments;
use \packages\base\view;
use \packages\userpanel\logs;
class delete extends logs{
	public function getColor():string{
		return "circle-bricky";
	}
	public function getIcon():string{
		return "fa fa-bank";
	}
	public function buildFrontend(view $view){}
}
