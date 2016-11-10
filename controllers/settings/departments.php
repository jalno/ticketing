<?php
namespace packages\ticketing\controllers\settings;
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\NotFound;
use \packages\base\http;
use \packages\base\db;
use \packages\base\IO;
use \packages\base\packages;
use \packages\base\views\FormError;
use \packages\base\inputValidation;
use \packages\base\response\file as responsefile;

use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;

use \packages\ticketing\controller;
use \packages\ticketing\authorization;
use \packages\userpanel\authentication;

use \packages\ticketing\view;
use \packages\ticketing\ticket;
use \packages\ticketing\department;
use \packages\ticketing\ticket_message;
use \packages\ticketing\ticket_param;
use \packages\ticketing\products;
use \packages\ticketing\ticket_file;

class departments extends controller{
	protected $authentication = true;
	public function listview(){
		authorization::haveOrFail('department_list');
		$view = view::byName("\\packages\\ticketing\\views\\settings\\department\\listview");
		$department = new department;
		$inputsRules = array(
			'id' => array(
				'type' => 'number',
				'optional' => true,
				'empty' => true
			),
			'title' => array(
				'type' => 'string',
				'optional' =>true,
				'empty' => true
			),
			'word' => array(
				'type' => 'string',
				'optional' => true,
				'empty' => true
			),
			'comparison' => array(
				'values' => array('equals', 'startswith', 'contains'),
				'default' => 'contains',
				'optional' => true
			)
		);
		$this->response->setStatus(true);
		try{
			$inputs = $this->checkinputs($inputsRules);
			foreach(array('id', 'title') as $item){
				if(isset($inputs[$item]) and $inputs[$item]){
					$comparison = $inputs['comparison'];
					if(in_array($item, array('id', 'status'))){
						$comparison = 'equals';
					}
					$department->where($item, $inputs[$item], $comparison);
				}
			}
			if(isset($inputs['word']) and $inputs['word']){
				$parenthesis = new parenthesis();
				foreach(array('title') as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where($item,$inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				$department->where($parenthesis);
			}
		}catch(inputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$view->setDataForm($this->inputsvalue($inputs));
		$department->pageLimit = $this->items_per_page;
		$departments = $department->paginate($this->page);
		$view->setDataList($departments);
		$view->setPaginate($this->page, $department->totalCount, $this->items_per_page);
		$this->response->setView($view);
		return $this->response;
	}
	public function delete($data){
		authorization::haveOrFail('department_delete');
		$view = view::byName("\\packages\\ticketing\\views\\settings\\department\\delete");
		$department = department::byId($data['id']);
		if(!$department){
			throw new NotFound;
		}
		$view->setDepartmentData($department);
		$this->response->setStatus(false);
		if(http::is_post()){
			try{
				$department->delete();
				$this->response->setStatus(true);
				$this->response->GO(userpanel\url("settings/departments"));
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
}
