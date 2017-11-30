<?php
namespace packages\ticketing\logs\tickets;
use \packages\base\view;
use \packages\userpanel\logs;
class lock extends logs{
	public function getColor():string{
		return "";
	}
	public function getIcon():string{
		return "fa fa-lock";
	}
	public function buildFrontend(view $view){}
}
