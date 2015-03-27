<?php 

namespace MageTest\Manager\Storage;

use Mage_Core_Model_Abstract;
use stdClass;


/**
 * Class FileFixtureStorage
 *
 * @package MageTest\Manager\Storage
 */
class FileFixtureStorage implements Storage
{

    /**
     * @param Mage_Core_Model_Abstract $model
     * @return mixed|void
     */
    public function persistIdentifier(Mage_Core_Model_Abstract $model)
    {
        $fixture = new stdClass;
        $fixture->resourceName = $model->getResourceName();
        $fixture->id = $model->getId();
        file_put_contents(__DIR__ . '/storage', serialize($fixture) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * @return array
     */
    public function getAllIdentifiers()
    {
        $fixtures = file_get_contents(__DIR__ . '/storage');
        $models = [];
        \Mage::app()->setCurrentStore(\Mage_Core_Model_App::ADMIN_STORE_ID);
        foreach (explode("\n", $fixtures) as $model) {
            if (empty($model)) continue;
            $models[] = $this->loadModel($model);
        }
        return $models;
    }

    /**
     *  Truncate storage
     */
    public function truncate()
    {
        unlink(__DIR__ . '/storage');
    }

    /**
     * @return bool
     */
    public function hasData()
    {
        return file_exists(__DIR__ . '/storage');
    }

    /**
     * @param $model
     * @return mixed
     */
    private function loadModel($model)
    {
        $model = unserialize($model);
        return \Mage::getModel($model->resourceName)->load($model->id);
    }

}
