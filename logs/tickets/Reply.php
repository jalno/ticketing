<?php
namespace packages\ticketing\logs\tickets;

use packages\base\View;
use packages\userpanel\Logs;

class Reply extends Logs {
	public function getColor():string{
		return "circle-green";
	}
	public function getIcon():string{
		return "fa fa-ticket";
	}
	public function buildFrontend(view $view){}
}
