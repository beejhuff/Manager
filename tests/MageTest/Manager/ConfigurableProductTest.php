<?php
namespace MageTest\Manager;

use Mage_Catalog_Model_Product_Type;

class ConfigurableProductTest extends WebTestCase
{
    private $configurableProductFixture;

    protected function setUp()
    {
        parent::setUp();
        Factory::prepareDb();
        $this->configurableProductFixture = Factory::make('catalog/product', [
                'type_id' => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            ]
        );
    }

    protected function tearDown()
    {
        Factory::clear();
    }

    public function testCreateConfigurableProduct()
    {
//        $session = $this->getSession();
//        $session->visit(getenv('BASE_URL') . '/catalog/product/view/id/' . $this->configurableProductFixture->getId());
//
//        $this->assertSession()->statusCodeEquals(200);
        $this->assertEquals('test', $this->configurableProductFixture->getName());
    }
}