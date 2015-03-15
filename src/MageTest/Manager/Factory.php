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
     */
    public function __construct($fixtureManager = null, $provider = null)
    {
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
        return (new static)->fixtureManager->loadFixture($model, $fixtureFile, $overrides);
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

}
