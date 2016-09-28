<?php
namespace themes\apital;
trait viewTrait{
	protected $shortitle;
	public function setShortTitle($title){
		$this->shortitle = $title;
	}
	public function getShortTitle(){
		if($this->shortitle){
			return $this->shortitle;
		}elseif($this->title){
			return $this->title[count($this->title)-1];
		}else{
			return null;
		}
	}
}
