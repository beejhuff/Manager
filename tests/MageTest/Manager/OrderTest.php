<?php
namespace MageTest\Manager;

class OrderTest extends WebTestCase
{
    private $orderFixture;

    protected function setUp()
    {
        parent::setUp();
        $this->orderFixture = $this->manager->loadFixture('sales/order');
    }

    public function testCreateOrderWithOneProduct()
    {
        $this->adminLogin('admin', 'password123');

        $session = $this->getSession();
        $session->getPage()->clickLink('Orders');
        $this->assertSession()->pageTextContains($this->orderFixture->getIncrementId());
    }

    public function testDeleteOrderWithOneProduct()
    {
        $this->manager->clear();

        $this->adminLogin('admin', 'password123');

        $session = $this->getSession();
        $session->getPage()->clickLink('Orders');
        $this->assertSession()->pageTextNotContains($this->orderFixture->getIncrementId());
    }
}