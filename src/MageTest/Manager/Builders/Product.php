<?php

namespace MageTest\Manager\Builders;

use Mage;

/**
 * Class Product
 * @package MageTest\Manager\Builders
 */
class Product extends AbstractBuilder implements BuilderInterface
{
    const MAX_QTY_VALUE = 99999999.9999;

    /**
     * @return \Mage_Catalog_Model_Product
     */
    public function build()
    {
        if (!isset($this->attributes['website_ids'])) {
            $productData['website_ids'] = array();
        }

        if (Mage::app()->isSingleStoreMode()) {
            $this->model->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        }

        if (!isset($this->attributes['category_ids'])) {
            $this->setCategoryIds(array());
        }

        $this->model->addData($this->filterStockData($this->attributes));

        $this->model->getResource()->save($this->model);

        $this->setStockData();

        Mage::dispatchEvent(
            'catalog_product_prepare_save',
            array('product' => $this->model, 'request' => $this->attributes)
        );

        return $this->model;
    }

    /**
     * Filter product stock data
     *
     * @param array $attributes
     * @return null
     */
    protected function filterStockData(&$attributes)
    {
        if (is_null($attributes['stock_data'])) {
            return;
        }
        if (!isset($attributes['stock_data']['use_config_manage_stock'])) {
            $attributes['stock_data']['use_config_manage_stock'] = 0;
        }
        if (isset($attributes['stock_data']['qty']) && (float)$attributes['stock_data']['qty'] > self::MAX_QTY_VALUE) {
            $attributes['stock_data']['qty'] = self::MAX_QTY_VALUE;
        }
        if (isset($attributes['stock_data']['min_qty']) && (int)$attributes['stock_data']['min_qty'] < 0) {
            $attributes['stock_data']['min_qty'] = 0;
        }
        if (!isset($attributes['stock_data']['is_decimal_divided']) || $attributes['stock_data']['is_qty_decimal'] == 0) {
            $attributes['stock_data']['is_decimal_divided'] = 0;
        }
        return $attributes;
    }

    private function setStockData()
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->assignProduct($this->model)
            ->setData('stock_id', 1)
            ->setData('store_id', 1);

        foreach ($this->attributes['stock_data'] as $key => $value) {
            $stockItem->setData($key, $value);
        }

        $stockItem->save();
    }
}
