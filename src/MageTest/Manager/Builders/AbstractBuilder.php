<?php
namespace MageTest\Manager\Builders;

use Mage;
use MageTest\Manager\Cache\Storage;

/**
 * Class AbstractBuilder
 * @package MageTest\Manager\Builders
 */
abstract class AbstractBuilder
{
    /**
     * @var array
     */
    public $attributes;

    /**
     * @var false|\Mage_Core_Model_Abstract
     */
    public $model;

    /**
     * @var
     */
    private $storage;

    /**
     * @param                                 $modelType
     * @param \MageTest\Manager\Cache\Storage $storage
     */
    public function __construct($modelType, Storage $storage)
    {
        $this->attributes = array();
        $this->model = Mage::getModel($modelType);
        $this->storage = $storage;
    }

    /**
     * @param $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getWebsiteIds()
    {
        $ids = array();
        foreach (Mage::getModel('core/website')->getCollection() as $website) {
            $ids[] = $website->getId();
        }
        return $ids;
    }

    public function saveModel($model)
    {
        $this->storage->persistIdentifier($model);
        return $model->save();
    }

    public function acceptsMultipleDependencyInstances()
    {
        return [];
    }

}
