<?php

namespace MageTest\Manager\Builders;

use Mage;
use Mage_Sales_Model_Order;

/**
 * Class Order
 * @package MageTest\Manager\Builders
 */
class Order extends AbstractBuilder implements BuilderInterface
{
    protected $quote;

    public function withQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * Build fixture model
     */
    public function build()
    {
        Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_ENABLED, '0');
        $service = Mage::getModel('sales/service_quote', $this->quote);
        $service->submitAll();
        return $service->getOrder();
    }

}
