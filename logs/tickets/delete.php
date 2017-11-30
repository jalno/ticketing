<?php
namespace packages\ticketing\logs\tickets;
use \packages\base\view;
use \packages\userpanel\logs;
class delete extends logs{
	public function getColor():string{
		return "circle-bricky";
	}
	public function getIcon():string{
		return "fa fa-ticket";
	}
	public function buildFrontend(view $view){}
}
