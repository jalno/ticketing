<?php
namespace themes\clipone\views\ticketing\settings\department;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;

use \packages\ticketing\views\settings\department\edit as departmentEdit;

use \packages\userpanel;

use \themes\clipone\views\listTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\breadcrumb;
use \themes\clipone\navigation\menuItem;

use \packages\ticketing\ticket;

class edit extends departmentEdit{
	use viewTrait,listTrait,formTrait;
	protected $messages;
	function __beforeLoad(){
		$this->setTitle(array(
			translator::trans('settings'),
			translator::trans('departments'),
			translator::trans('department_edit')
		));
		navigation::active("settings/departments/list");
	}
}
