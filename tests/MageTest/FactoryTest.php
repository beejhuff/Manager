<?php

namespace MageTest;

use MageTest\Manager\Factory;
use MageTest\Manager\WebTestCase;
use PHPUnit_Framework_TestCase;

class FactoryTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        Factory::clear();
        parent::tearDown();
    }

    public function testCreateSimpleProduct()
    {
        $products = Factory::times(3)->make('catalog/product', ['name' => 'foo']);

        $this->assertEquals(3, count($products));
        foreach ($products as $product) {
            $this->assertEquals('foo', $product->getName());
        }
    }

    public function testSettingDependencyExplicitly()
    {
        $customer = Factory::make('customer/customer', ['firstname' => 'foobar']);
        $addresses = Factory::with($customer)->times(2)->make('customer/address');
        $this->assertTrue(is_array($addresses));
        $this->assertCount(2, $addresses);
        $this->assertEquals(end($addresses)->getFirstname(), 'foobar');
    }

    public function testCreateAddress()
    {
        $address = Factory::make('customer/address', ['city' => 'Stockholm', 'company' => 'Karlsson & Lord']);

        $this->assertEquals('Karlsson & Lord', $address->getCompany());
        $this->assertEquals('Stockholm', $address->getCity());
    }

    public function testCreateOrder()
    {
        $orders = Factory::times(2)->make('sales/quote', ['customer_email' => 'test@test.de']);

        $this->assertCount(2, $orders);
        $this->assertInstanceOf('Mage_Sales_Model_Order', reset($orders));
    }

}
 