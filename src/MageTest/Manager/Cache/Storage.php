<?php 

namespace MageTest\Manager\Cache;


/**
 * Interface Storage
 *
 * @package MageTest\Manager\Cache
 */
interface Storage
{
    /**
     * @param $model
     * @return mixed
     */
    public function persistIdentifier($model);

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