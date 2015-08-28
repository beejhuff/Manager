<?php 

namespace MageTest\Manager\Builders;


use MageTest\Manager\Cache\Storage;

class BuilderFactory
{
    public static function getBuilder($resourceName, Storage    $storage)
    {
        switch ($resourceName) {
            case 'admin/user':
                return new Admin($resourceName, $storage);
            case 'customer/address':
                return new Address($resourceName, $storage);
            case 'customer/customer':
                return new Customer($resourceName, $storage);
            case 'catalog/product':
                return new Product($resourceName, $storage);
            case 'catalog/category':
                return new Category($resourceName, $storage);
            case 'sales/quote':
                return new Quote($resourceName, $storage);
            case 'sales/order':
                return new Order($resourceName, $storage);
            default:
                return new General($resourceName, $storage);
        }
    }
}