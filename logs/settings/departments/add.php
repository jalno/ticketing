<?php
namespace packages\ticketing\logs\settings\departments;
use \packages\base\view;
use \packages\userpanel\logs;
class add extends logs{
	public function getColor():string{
		return "circle-green";
	}
	public function getIcon():string{
		return "fa fa-bank";
	}
	public function buildFrontend(view $view){}
}
