<?php 

namespace MageTest\Manager\Builders;


class BuilderFactory
{
    public function getBuilder($resourceName, $storage)
    {
        switch ($resourceName) {
            case 'admin/user':
                return $this->builders[$resourceName] = new Admin($resourceName, $storage);
            case 'customer/address':
                return $this->builders[$resourceName] = new Address($resourceName, $storage);
            case 'customer/customer':
                return $this->builders[$resourceName] = new Customer($resourceName, $storage);
            case 'catalog/product':
                return $this->builders[$resourceName] = new Product($resourceName, $storage);
            case 'catalog/category':
                return $this->builders[$resourceName] = new Category($resourceName, $storage);
            case 'sales/quote':
                return $this->builders[$resourceName] = new Quote($resourceName, $storage);
            case 'sales/order':
                return $this->builders[$resourceName] = new Order($resourceName, $storage);
            default:
                return $this->builders[$resourceName] = new General($resourceName, $storage);
        }
    }

}