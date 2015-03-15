<?php 

namespace MageTest\Manager;

use MageTest\Manager\Attributes\Provider\AttributesProvider;
use MageTest\Manager\Attributes\Provider\PhpProvider;
use MageTest\Manager\Attributes\Provider\YamlProvider;

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
    protected $fixtureManager;

    /**
     * @var
     */
    protected static $model;

    /**
     * @param null $fixtureManager
     * @param null $provider
     * @param null $multiplier
     */
    public function __construct($fixtureManager = null, $provider = null, $multiplier = null)
    {
        static::$multiplier = $multiplier;
        $this->fixtureManager = $fixtureManager ? : new FixtureManager(new AttributesProvider);
    }

    /**
     * @param        $model
     * @param array  $overrides
     * @param null   $fixtureFile
     * @return mixed
     */
    public static function make($model, array $overrides = array(), $fixtureFile = null)
    {
        return (new static(null, null, static::$multiplier))->fixtureManager->loadFixture($model, $fixtureFile, $overrides, static::$multiplier);
    }

    /**
     * @return mixed
     */
    public static function getModel()
    {
        return static::$model;
    }

    /**
     *  Wipe all the models from the manager and the db
     */
    public static function clear()
    {
        static::$fixtureManager->clear();
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

}
