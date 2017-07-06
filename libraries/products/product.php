<?php
namespace packages\ticketing;
use \packages\userpanel\user;
use \packages\ticketing\product\box;
abstract class product{
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
	abstract public function getServices(user $client);
	abstract public function showInformationBox(user $client, int $id);
	public function getServiceById(user $client, $id){
		foreach($this->getServices($client) as $service){
			if($service->getId() == $id){
				return $service;
			}
		}
		return null;
	}
	public function addBox(box $box){
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
			$html .= "<div class=\"row\">";
			foreach($row as $box){
				$html .= "<div class=\"col-sm-12\">".$box->getHTML()."</div>";
			}
			$html .= "</div>";
		}
		return $html;
	}
}
