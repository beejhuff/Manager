<?php

namespace MageTest\Manager\Attributes\Provider;

/**
 * Interface ProviderInterface
 * @package MageTest\Manager\Attributes\Provider
 */
interface ProviderInterface
{
    /*
     * Reads file from provider returning attributes for magento model creation
     * @return mixed
     */
    public function readAttributes();
    /*
     * Returns magento model required for fixture
     * @return mixed
     */
    public function getResourceName();

    /*
     * Reads fixture attributes from file
     * @param $file
     * @return mixed
     */
    public function readFile($file);

    /*
     * Checks if fixture has dependencies
     * @return bool
     */
    public function hasFixtureDependencies();

    /*
     * Returns a array of fixture dependencies
     * @return mixed
     */
    public function getFixtureDependencies();

}