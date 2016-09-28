<?php
namespace packages\ticketing\views;
use \packages\ticketing\views\listview as list_view;
use \packages\ticketing\authorization;

class ticketlist extends list_view{
	protected $canAdd;
	protected $canView;
	protected $canEdit;
	protected $canDel;
	static protected $navigation;
	function __construct(){
		$this->canAdd = authorization::is_accessed('add');
		$this->canView = authorization::is_accessed('view');
		$this->canEdit = authorization::is_accessed('edit');
		$this->canDel = authorization::is_accessed('delete');
	}

	public static function onSourceLoad(){
		self::$navigation = authorization::is_accessed('list');
	}
}
