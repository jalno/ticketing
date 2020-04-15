<?php
namespace packages\ticketing\views\settings\department;
use \packages\ticketing\views\listview as list_view;
use \packages\ticketing\authorization;
use \packages\base\views\traits\form as formTrait;
class listview extends list_view{
	use formTrait;
	protected $canAdd;
	protected $canEdit;
	protected $canDel;
	static protected $navigation;
	function __construct(){
		$this->canAdd = authorization::is_accessed('settings_departments_add');
		$this->canEdit = authorization::is_accessed('settings_departments_edit');
		$this->canDel = authorization::is_accessed('settings_departments_delete');
	}
	public function getDepartments(){
		return $this->dataList;
	}
	public static function onSourceLoad(){
		self::$navigation = authorization::is_accessed('settings_departments_list');
	}
}
