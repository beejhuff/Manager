<?php 

namespace MageTest\Manager\Attributes\Provider\Loader;


/**
 * Interface Loader
 *
 * @package MageTest\Manager\Attributes\Provider\Loader
 */
interface Loader
{
    /**
     * @param $file
     * @return mixed
     */
    public function load($file);
} 