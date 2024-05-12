<?php
namespace packages\ticketing;
use \packages\ticketing\Events\ProductList;
use \packages\base\Events;
class Products{
	static protected $products = array();
	static public function add(Product $product){
		self::$products[] = $product;
	}
	static public function get(){
		Events::trigger(new ProductList());
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
