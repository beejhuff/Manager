<?php

namespace MageTest\Manager\Builders;

use Mage;
use Mage_Core_Model_Abstract;
use Mage_Sales_Model_Order;
use RuntimeException;

/**
 * Class Order
 * @package MageTest\Manager\Builders
 */
class Order extends AbstractBuilder implements BuilderInterface
{
    /**
     * @param $quote
     * @return $this
     */
    public function withQuote($quote)
    {
        $this->model = $quote;
        return $this;
    }


    /**
     * Build fixture model
     */
    public function build()
    {
        $this->model->setData($this->attributes);
        $this->model->collectTotals()->save();
        Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_ENABLED, '0');
        $service = Mage::getModel('sales/service_quote', $this->model);
        $service->submitAll();
        return $service->getOrder();
    }

}
