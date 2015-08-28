<?php


use MageTest\Manager\Builders\BuilderFactory;
use MageTest\Manager\Cache\FileFixtureStorage;

class BuilderFactoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Mage::app()->setCurrentStore(1);
    }
    protected $builders = [
        'admin/user' => 'MageTest\Manager\Builders\Admin',
        'customer/address' => 'MageTest\Manager\Builders\Address',
        'customer/customer' => 'MageTest\Manager\Builders\Customer',
        'catalog/product' => 'MageTest\Manager\Builders\Product',
        'catalog/category' => 'MageTest\Manager\Builders\Category',
        'sales/quote' => 'MageTest\Manager\Builders\Quote',
        'sales/order' => 'MageTest\Manager\Builders\Order',
        'foo/bar' => 'MageTest\Manager\Builders\General'
    ];
    /**
     * @test
     */
    public function it_returns_the_correct_builder_types()
    {
        $storage = new FileFixtureStorage();
        foreach ($this->builders as $resourceName => $classPath) {
            $path = BuilderFactory::getBuilder($resourceName, $storage);
            $this->assertEquals(get_class($path), $classPath);
        }
    }
}
