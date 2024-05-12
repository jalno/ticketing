<?php

namespace packages\ticketing;

use packages\base\Events;
use packages\ticketing\Events\ProductList;

class Products
{
    protected static $products = [];

    public static function add(Product $product)
    {
        self::$products[] = $product;
    }

    public static function get()
    {
        Events::trigger(new ProductList());

        return self::$products;
    }

    public static function getOne($name)
    {
        foreach (self::$products as $product) {
            if ($product->getName() == $name) {
                return $product;
            }
        }

        return null;
    }

    public static function has($name)
    {
        foreach (self::$products as $product) {
            if ($product->getName() == $name) {
                return true;
            }
        }

        return false;
    }
}
