<?php 

namespace MageTest\Manager\Storage;

use Mage_Core_Model_Abstract;


/**
 * Interface Storage
 *
 * @package MageTest\Manager\Storage
 */
interface Storage
{
    /**
     * @param $model
     * @return mixed
     */
    public function persistIdentifier(Mage_Core_Model_Abstract $model);

    /**
     * @return mixed
     */
    public function getAllIdentifiers();

    /**
     * @return mixed
     */
    public function truncate();

    /**
     * @return mixed
     */
    public function hasData();
} 