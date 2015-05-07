<?php

namespace MageTest\Manager\Builders;

/**
 * Class Customer
 * @package MageTest\Manager\Builders
 */
class Category extends AbstractBuilder implements BuilderInterface
{
    /**
     * @return false|\Mage_Core_Model_Abstract
     */
    public function build()
    {
        $this->model->addData($this->attributes);
        $parentCategory = \Mage::getModel('catalog/category')->load(
            \Mage::app()->getStore()->getRootCategoryId()
        );
        $this->model->setPath($parentCategory->getPath());
        $this->model->save();
        return $this->model;
    }
}