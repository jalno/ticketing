<?php
namespace packages\ticketing;
use \packages\userpanel\User;
use \packages\ticketing\Product\Box;
abstract class Product{
	protected $name;
	protected $title;
	private $boxs = [];
	public function setName($name){
		$this->name = $name;
	}
	public function getName(){
		return $this->name;
	}
	public function getTitle(){
		return $this->title;
	}
	abstract public function getServices(User $client);
	abstract public function showInformationBox(User $client, int $id);
	public function getServiceById(User $client, $id){
		foreach($this->getServices($client) as $service){
			if($service->getId() == $id){
				return $service;
			}
		}
		return null;
	}
	public function addBox(Box $box){
		$this->boxs[] = $box;
	}
	public function getBoxs(){
		return $this->boxs;
	}
	public function generateRows(){
		$rows = [];
		$lastrow = 0;
		foreach($this->boxs as $box){
			$rows[$lastrow][] = $box;
			$size = 0;
			foreach($rows[$lastrow] as $rowbox){
				$size += $rowbox->size;
			}
			if($size >= 12){
				$lastrow++;
			}
		}
		$html = '';
		foreach($rows as $row){
			foreach($row as $box){
				$html .= $box->getHTML();
			}
		}
		return $html;
	}
}
