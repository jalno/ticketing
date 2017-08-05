<?php
namespace packages\ticketing\controllers\userpanel;
use \packages\userpanel\user;
use \packages\ticketing\controller;
use \packages\base\inputValidation;
class settings extends controller{
	protected $authentication = true;
	public function store(array $inputs, user $user){
		if(isset($inputs['ticketing_editor'])){
			$user->setOption('ticketing_editor', $inputs['ticketing_editor']);
		}
	}
}
