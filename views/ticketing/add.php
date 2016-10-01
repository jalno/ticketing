<?php
namespace packages\ticketing\views;

class add extends \packages\ticketing\views\form{
	public function setMessageData($data){
		$this->setData($data, 'ticket');
	}
	public function getMessageData(){
		return $this->getData('ticket');
	}
	public function setDepartmentData($data){
		$this->setData($data, 'department');
	}
	public function getDepartmentData(){
		return $this->getData('department');
	}
	public function setProducts($products){
		$this->setData($products, 'products');
	}
	public function getProducts(){
		return $this->getData('products');
	}
}
