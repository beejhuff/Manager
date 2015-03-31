<?php

namespace MageTest\Manager\Builders;

use Mage_Catalog_Model_Product;
use Mage_Catalog_Model_Product_Type;

/**
 * Class Product
 *
 * @package MageTest\Manager\Builders
 */
class Product extends AbstractBuilder implements BuilderInterface
{
    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function withProduct(Mage_Catalog_Model_Product $product)
    {
        die('test');
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function build()
    {
        $this->model->setStockItem(\Mage::getModel('cataloginventory/stock_item'));
        return $this->model->addData($this->attributes);
    }

    /**
     * @param $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        if ($this->attributes['type_id'] === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $this->dependencies = ['catalog/product'];
        }
    }

}
