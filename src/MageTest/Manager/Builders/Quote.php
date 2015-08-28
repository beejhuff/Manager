<?php 

namespace MageTest\Manager\Builders;


class Quote extends AbstractBuilder implements BuilderInterface
{

    /**
     * @param \Mage_Catalog_Model_Product $product
     * @param int $qty
     * @return $this
     */
    public function withProduct($product, $qty = 1)
    {
        $this->model->addProduct($product, new \Varien_Object(array(
            'qty' => $qty
        )));
        return $this;
    }

    /**
     * @param \Mage_Customer_Model_Customer $customer
     * @return $this
     */
    public function withCustomer($customer)
    {
        $this->model->assignCustomer($customer);
        return $this;
    }

    /**
     * @param \Mage_Customer_Model_Address $address
     * @return $this
     */
    public function withAddress($address)
    {
        $this->model->getBillingAddress()->addData($address->getData());
        $this->model->getShippingAddress()->addData($address->getData())
            ->setCollectShippingRates(true)->collectShippingRates()
            ->setShippingMethod($this->attributes['shipping_method'])
            ->setPaymentMethod($this->attributes['payment_method']);
        return $this;
    }

    /**
     * Build fixture model
     */
    public function build()
    {
        $this->model->setData($this->attributes);
        $this->model->setStoreId($this->model->getStoreId());
        $this->model->getPayment()->importData(array('method' => $this->attributes['payment_method']));
        $this->model->collectTotals()->save();
        if (!$this->model instanceof Mage_Core_Model_Abstract) {
            throw new RuntimeException("Unable to save '{$this->model->getResourceName()}' to database.");
        }
        Mage::app()->getStore()->setConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_ENABLED, '0');
        return $this->model;
    }
}