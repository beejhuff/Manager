<?php

namespace MageTest\Manager;

use MageTest\Manager\Attributes\Provider\AttributesProvider;

/**
 * Class Factory
 *
 * @package MageTest\Manager
 */
class Factory
{
    /**
     * @var
     */
    public static $multiplier = 0;

    private static $with;

    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    /**
     * @var
     */
    protected static $model;

    /**
     * @param \MageTest\Manager\FixtureManager|null    $fixtureManager
     * @param \MageTest\Manager\ProviderInterface|null $provider
     * @param null                                     $multiplier
     * @param null                                     $with
     */
    public function __construct(
        FixtureManager $fixtureManager = null,
        ProviderInterface $provider = null,
        $multiplier = null,
        $with = null
    ) {
        static::$multiplier = $multiplier;
        static::$with = $with;
        $this->fixtureManager = $fixtureManager ? : new FixtureManager($provider ? : new AttributesProvider);
    }

    /**
     * @param        $resourceName
     * @param array  $overrides
     * @param null   $fixtureFile
     * @return mixed
     */
    public static function make($resourceName, array $overrides = array(), $fixtureFile = null)
    {
        return (new static(null, null, static::$multiplier, static::$with))
            ->fixtureManager
            ->setMultiplierId($resourceName)
            ->setFixtureDependency(static::$with)
            ->loadFixture($resourceName, $fixtureFile, $overrides, static::$multiplier)
            ;
    }

    /**
     * @param \Mage_Core_Model_Abstract|array $model
     * @return static
     */
    public static function with($model)
    {
        return new static(null, null, null, $model);
    }

    /**
     *  Wipe all the models from the manager and the db
     */
    public static function clear()
    {
        return (new static)->fixtureManager->clear();
    }

    /**
     * @param $multiplier
     * @return static
     */
    public static function times($multiplier)
    {
        return new static(null, null, $multiplier, static::$with);
    }

    /**
     *  Reset counter
     */
    public static function resetMultiplier()
    {
        static::$multiplier = 0;
    }

    /**
     * @return mixed
     */
    public static function prepareDb()
    {
        return (new static)->fixtureManager->prepareDb();
    }

    /**
     * @param \Mage_Core_Model_Abstract $model
     * @return mixed
     */
    public static function setFixture(\Mage_Core_Model_Abstract $model)
    {
        return (new static)->fixtureManager->setFixture($model);
    }

    /**
     *
     */
    public static function unsetFixtures()
    {
        FixtureManager::$globalFixtureRegistry = [];
    }

}
