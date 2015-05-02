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
    private static $multiplier;

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
     */
    public function __construct(
        FixtureManager $fixtureManager = null,
        ProviderInterface $provider = null,
        $multiplier = null
    ) {
        static::$multiplier = $multiplier;
        $this->fixtureManager = $fixtureManager ? : new FixtureManager($provider ? : new AttributesProvider);
    }

    /**
     * @param        $model
     * @param array  $overrides
     * @param null   $fixtureFile
     * @return mixed
     */
    public static function make($model, array $overrides = array(), $fixtureFile = null)
    {
        return (new static(null, null, static::$multiplier))
            ->fixtureManager->loadFixture($model, $fixtureFile, $overrides, static::$multiplier);
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
        return new static(null, null, $multiplier);
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

}
