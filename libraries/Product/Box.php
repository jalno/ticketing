<?php
namespace packages\ticketing\Product;
class Box{
	public $name;
	public $icon;
	public $priority = 0;
	public $size = 12;
	public $html = '';
	function __construct($name){
		$this->name = $name;
	}
	public function setHTML($html){
		$this->html = $html;
	}
	public function getHTML(){
		return $this->html;
	}
}
