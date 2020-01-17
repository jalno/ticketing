<?php
namespace themes\clipone\views\ticketing\settings\department;

use packages\base;
use packages\base\view\Error;
use packages\userpanel;
use themes\clipone\{navigation\MenuItem, Navigation, ViewTrait};
use themes\clipone\views\{DepartmentTrait, FormTrait, ListTrait};
use packages\ticketing\views\settings\department\listview as DepartmentList;

class listview extends DepartmentList {
	use DepartmentTrait, FormTrait, ListTrait, ViewTrait;

	function __beforeLoad() {
		$this->setTitle(t("departments"));
		$this->setButtons();
		$this->onSourceLoad();
		Navigation::active("settings/departments/list");
		if (empty($this->getDepartments())) {
			$this->addNotFoundError();
		}
	}
	private function addNotFoundError() {
		$error = new Error();
		$error->setType(Error::NOTICE);
		$error->setCode('ticketing.settings.department.notfound');
		$error->setMessage('ticketing.settings.department.notfound');
		if ($this->canAdd) {
			$error->setData([
				[
					'type' => 'btn-teal',
					'txt' => t('add'),
					'link' => userpanel\url('settings/departments/add')
				]
			], 'btns');
		}
		$this->addError($error);
	}
	public function setButtons() {
		$this->setButton('edit', $this->canEdit, array(
			'title' => t('department.edit'),
			'icon' => 'fa fa-edit',
			'classes' => array('btn', 'btn-xs', 'btn-teal')
		));
		$this->setButton('delete', $this->canDel, array(
			'title' => t('department.delete'),
			'icon' => 'fa fa-times',
			'classes' => array('btn', 'btn-xs', 'btn-bricky')
		));
	}

	public function getComparisonsForSelect() {
		return array(
			array(
				'title' => t('search.comparison.contains'),
				'value' => 'contains'
			),
			array(
				'title' => t('search.comparison.equals'),
				'value' => 'equals'
			),
			array(
				'title' => t('search.comparison.startswith'),
				'value' => 'startswith'
			)
		);
	}
	public static function onSourceLoad(){
		parent::onSourceLoad();
		if(parent::$navigation){
			if($item = navigation::getByName("settings")){
				$departments = new menuItem("departments");
				$departments->setTitle(t('departments'));
				$departments->setURL(userpanel\url('settings/departments'));
				$departments->setIcon('fa fa-university');
				$item->addItem($departments);
			}
		}
	}
}
