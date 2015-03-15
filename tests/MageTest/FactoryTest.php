<?php

namespace MageTest;

use MageTest\Manager\Factory;
use MageTest\Manager\WebTestCase;

/**
 * 1. Check for custom php template
 */
class FactoryTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testCreateSimpleProduct()
    {
        $product = Factory::make('catalog/product', ['name' => 'foo']);
        $this->assertEquals('foo', $product->getName());
    }

    public function testCreateAddress()
    {
        $address = Factory::make('customer/address', ['city' => 'Stockholm']);

        $this->assertEquals('Session Digital', $address->getCompany());
        $this->assertEquals('Stockholm', $address->getCity());
    }
//
//    public function testCreateOrder()
//    {
//        $order = Factory::make('sales/quote', ['customer_email' => 'test@test.de']);
//
//        $this->assertEquals('test@test.de', $order->getCustomerEmail());
//    }

}
 