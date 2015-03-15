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
        $products = Factory::times(3)->make('catalog/product', ['name' => 'foo']);
        $this->assertEquals(3, count($products));
        foreach ($products as $product) {
            $this->assertEquals('foo', $product->getName());
        }
    }

    public function testCreateAddress()
    {
        $address = Factory::make('customer/address', ['city' => 'Stockholm']);

        $this->assertEquals('Session Digital', $address->getCompany());
        $this->assertEquals('Stockholm', $address->getCity());
    }

    public function testCreateOrder()
    {
        $orders = Factory::times(2)->make('sales/quote', ['customer_email' => 'test@test.de']);

        $this->assertCount(2, $orders);
        $this->assertInstanceOf('Mage_Sales_Model_Order', $orders[0]);
    }

}
 