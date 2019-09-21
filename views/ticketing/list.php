<?php
namespace packages\ticketing\views;

use packages\financial\views\listview as list_view;
use packages\base\views\traits\form as formTrait;
use packages\ticketing\authorization;

class ticketlist extends list_view {
	protected $canAdd;
	protected $canView;
	protected $canEdit;
	protected $canDel;
	protected $multiuser;
	protected $isTab = false;
	static protected $navigation;
	
	function __construct(){
		$this->canAdd = authorization::is_accessed('add');
		$this->canView = authorization::is_accessed('view');
		$this->canEdit = authorization::is_accessed('edit');
		$this->canDel = authorization::is_accessed('delete');
		$this->multiuser = (bool)authorization::childrenTypes();
	}
	public function getTickets(){
		return $this->dataList;
	}
	public function setDepartment($department){
		$this->setData($department, 'department');
	}
	public function getDepartment(){
		return $this->getData('department');
	}
	public static function onSourceLoad(){
		self::$navigation = authorization::is_accessed('list');
	}
	public function isTab(bool $isTab = true): void {
		$this->isTab = $isTab;
	}
}
