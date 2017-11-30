<?php
namespace packages\ticketing\logs\tickets;
use \packages\base\view;
use \packages\userpanel\logs;
class unlock extends logs{
	public function getColor():string{
		return "circle-green";
	}
	public function getIcon():string{
		return "fa fa-unlock";
	}
	public function buildFrontend(view $view){}
}
