<?php
namespace packages\ticketing;
use \packages\userpanel\user;
abstract class product{
	protected $name;
	protected $title;
	public function setName($name){
		$this->name = $name;
	}
	public function getName(){
		return $this->name;
	}
	public function getTitle(){
		return $this->title;
	}
	abstract public function getServices(user $client);
	public function getServiceById(user $client, $id){
		foreach($this->getServices($client) as $service){
			if($service->getId() == $id){
				return $service;
			}
		}
		return null;
	}
}
