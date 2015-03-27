<?php 

namespace MageTest\Manager\Storage;

/**
 * Class ModelRepository
 *
 * @package MageTest\Manager\Storage
 */
class ModelRepository
{
    /**
     * @param \Mage_Core_Model_Abstract $model
     * @return \Mage_Core_Model_Resource_Db_Abstract
     */
    public function persist(\Mage_Core_Model_Abstract $model)
    {
        $model->_getResource()->beginTransaction();
        return $model->_getResource()->save($model);
    }

    /**
     * @param $model
     * @return mixed
     */
    public function rollBackTransaction($model)
    {
        return $model->_getResource()->rollBack();
    }

}
