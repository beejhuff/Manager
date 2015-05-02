<?php

namespace MageTest\Manager\Builders;

/**
 * Class Customer
 * @package MageTest\Manager\Builders
 */
class Customer extends AbstractBuilder implements BuilderInterface
{
    /**
     * @return false|\Mage_Core_Model_Abstract
     */
    public function build()
    {
        $this->model->addData($this->attributes);
        $this->saveModel($this->model);
        $this->model->setConfirmation(null);
        return $this->model;
    }
}