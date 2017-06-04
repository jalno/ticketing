<?php
namespace packages\ticketing\controllers\settings;
use \packages\base\db;
use \packages\base\http;
use \packages\base\NotFound;
use \packages\base\translator;
use \packages\base\view\error;
use \packages\base\utility\safe;
use \packages\base\views\FormError;
use \packages\base\inputValidation;
use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;
use \packages\ticketing\controller;
use \packages\ticketing\authorization;
use \packages\userpanel\authentication;
use \packages\ticketing\ticket;
use \packages\ticketing\view;
use \packages\ticketing\department;
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
		$department = department::byId($data['id']);
		if(!$department){
			throw new NotFound;
		}
		$view = view::byName("\\packages\\ticketing\\views\\settings\\department\\delete");
		$view->setDepartmentData($department);
		$this->response->setStatus(false);
		if(http::is_post()){
			try{
				$ticket = new ticket();
				$ticket->where('department', $department->id);
				if($ticket->has()){
					throw new ticketDependencies();
				}
				$department->delete();
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url("settings/departments"));
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}catch(ticketDependencies $e){
				$error = new error();
				$error->setCode('ticketDependencies');
				$error->setMessage(translator::trans('error.ticketDependencies', ['ticket_search_link' => userpanel\url('ticketing', ['department'=>$department->id])]));
				$view->addError($error);
			}
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function add(){
		authorization::haveOrFail('department_add');
		$view = view::byName("\\packages\\ticketing\\views\\settings\\department\\add");
		$inputsRules = array(
			'title' => array(
				'type' => 'string'
			),
			'day' => array()
		);
		$this->response->setStatus(false);
		if(http::is_post()){
			try{
				$inputs = $this->checkinputs($inputsRules);
				$department = new department;
				if(is_array($inputs['day'])){
					foreach($inputs['day'] as $day => $val){
						if(!in_array($day, range(1,7))){
							throw new inputValidation("day[{$day}][enable]");
						}
						if(isset($val['enable']) and $val['enable']){
							if(!isset($val['worktime']['start']) or !in_array($val['worktime']['start'], range(0,23))){
								throw new inputValidation("day[{$day}][worktime][start]");
							}
							if(!isset($val['worktime']['end']) or !in_array($val['worktime']['end'], range(0,23))){
								throw new inputValidation("day[{$day}][worktime][end]");
							}
							if($val['worktime']['end'] < $val['worktime']['start']){
								throw new inputValidation("day[{$day}][worktime][end]");
							}
						}else{
							$inputs['day'][$day]['worktime']['start'] = $inputs['day'][$day]['worktime']['end'] = 0;
						}
						if(array_key_exists('message',$val)){
							$val['message'] = safe::string($val['message']);
						}else{
							throw new inputValidation("day[{$day}][message]");
						}
					}
				}else{
					throw new inputValidation("day");
				}
				if(isset($inputs['title'])){
					$department->title = $inputs['title'];
				}
				$department->save();
				foreach($department->worktimes as $work){
					$input = $inputs['day'][$work->day];
					$work->time_start = $input['worktime']['start'];
					$work->time_end = $input['worktime']['end'];
					$work->message = $input['message'];
					$work->save();
				}
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url("settings/departments/edit/".$department->id));
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function edit($data){
		(($this->response->is_ajax() and !http::is_post()) or authorization::haveOrFail('department_edit'));
		$view = view::byName("\\packages\\ticketing\\views\\settings\\department\\edit");
		$department = department::byId($data['id']);
		if(!$department){
			throw new NotFound;
		}
		$view->setDepartment($department);
		$inputsRules = array(
			'title' => array(
				'type' => 'string',
				'optional' => true
			),
			'day' => array()
		);
		$this->response->setStatus(false);
		if(http::is_post()){
			try{
				$inputs = $this->checkinputs($inputsRules);
				if(is_array($inputs['day'])){
					foreach($inputs['day'] as $day => $val){
						if(!in_array($day, range(1,7))){
							throw new inputValidation("day[{$day}][enable]");
						}
						if(isset($val['enable']) and $val['enable']){
							if(!isset($val['worktime']['start']) or !in_array($val['worktime']['start'], range(0,23))){
								throw new inputValidation("day[{$day}][worktime][start]");
							}
							if(!isset($val['worktime']['end']) or !in_array($val['worktime']['end'], range(0,23))){
								throw new inputValidation("day[{$day}][worktime][end]");
							}
							if($val['worktime']['end'] < $val['worktime']['start']){
								throw new inputValidation("day[{$day}][worktime][end]");
							}
						}else{
							$inputs['day'][$day]['worktime']['start'] = $inputs['day'][$day]['worktime']['end'] = 0;
						}
						if(array_key_exists('message',$val)){
							$val['message'] = safe::string($val['message']);
						}else{
							throw new inputValidation("day[{$day}][message]");
						}
					}
				}else{
					throw new inputValidation("day");
				}
				if(isset($inputs['title'])){
					$department->title = $inputs['title'];
				}
				foreach($department->worktimes as $work){
					$input = $inputs['day'][$work->day];
					$work->time_start = $input['worktime']['start'];
					$work->time_end = $input['worktime']['end'];
					$work->message = $input['message'];
					$work->save();
				}
				$department->save();
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url("settings/departments/edit/".$department->id));
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
class ticketDependencies extends \Exception{}