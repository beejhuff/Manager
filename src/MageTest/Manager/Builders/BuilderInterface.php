<?php

namespace MageTest\Manager\Builders;

use MageTest\Manager\Cache\Storage;

/**
 *
 * @package MageTest\Manager\Builders
 */
interface BuilderInterface {
    /*
     * Magento model type required in construct e.g catalog/product
     * @param $modelType
     */
    /**
     * @param                                 $modelType
     * @param \MageTest\Manager\Cache\Storage $storage
     */
    public function __construct($modelType, Storage $storage);

    /**
     * Build fixture model
     */
    public function build();

    /**
     * @return array
     */
    public function acceptsMultipleDependencyInstances();
}
