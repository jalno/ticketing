<?php
namespace packages\ticketing;
use \packages\ticketing\events\product_list;
use \packages\base\events;
class products{
	static protected $products = array();
	static public function add(product $product){
		self::$products[] = $product;
	}
	static public function get(){
		events::trigger(new product_list());
		return self::$products;
	}
	static public function getOne($name){
		foreach(self::$products as $product){
			if($product->getName() == $name){
				return $product;
			}
		}
		return null;
	}
	static public function has($name){
		foreach(self::$products as $product){
			if($product->getName() == $name){
				return true;
			}
		}
		return false;
	}
}
