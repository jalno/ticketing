<?php
namespace packages\ticketing\views;

use packages\userpanel\User;
use packages\ticketing\views\Form;

class Add extends Form {
	public function setClient(User $client) {
		$this->setData($client, 'client');
		$this->setDataForm($client->id, 'client');
		$this->setDataForm($client->getFullName(), 'client_name');
	}
	public function getClient() {
		return $this->getData('client');
	}
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
		return $this->getData('products') ?? [];
	}
}
