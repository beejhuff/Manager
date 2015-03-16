<?php
namespace MageTest\Manager;

use InvalidArgumentException;
use Mage;
use Mage_Core_Model_App;
use MageTest\Manager\Attributes\Provider\ProviderInterface;
use MageTest\Manager\Builders\BuilderInterface;
use MageTest\Manager\Builders;

/**
 * Class FixtureManager
 *
 * @package MageTest\Manager
 */
class FixtureManager
{
    /**
     *  Where the user has to store its project specific fixtures
     */
    const CUSTOM_FIXTURES_DIR = '/tests/fixtures';
    /**
     * @var array
     */
    private static $fixtures = array();

    /**
     * @var array
     */
    private $builders = array();

    /**
     * @var ProviderInterface
     */
    private $attributesProvider;

    /**
     * @param ProviderInterface $attributesProvider
     */
    public function __construct(ProviderInterface $attributesProvider)
    {
        $this->attributesProvider = $attributesProvider;
    }

    /**
     * @param       $fixtureType
     * @param null  $userFixtureFile
     * @param array $overrides
     * @param       $multiplier
     * @internal param $fixtureFile
     * @return mixed
     */
    public function loadFixture($fixtureType, $userFixtureFile = null, array $overrides = null, $multiplier = null)
    {
        $attributesProvider = clone $this->attributesProvider;
        if (!is_null($userFixtureFile)) {
            $this->fixtureFileExists($userFixtureFile);
            $attributesProvider->readFile($userFixtureFile);
        } else {
            $attributesProvider->readFile($this->getFallbackFixture($fixtureType));
        }
        // Override attributes and add non-existing ones too
        if ($overrides) {
            $attributesProvider->overrideAttributes($overrides);
        }

        // Fetch the correct builder instance
        $builder = $this->getBuilder($attributesProvider->getModelType());
        // Give the attributes for the builder to construct a model with
        $builder->setAttributes($attributesProvider->readAttributes());

        if ($attributesProvider->hasFixtureDependencies()) {
            // Load dependencies recursively
            foreach ($attributesProvider->getFixtureDependencies() as $dependency) {
                $withDependency = 'with' . $this->getDependencyModel($dependency);
                $builder->$withDependency($this->loadFixture($dependency));
            }
        }
        return $this->create($attributesProvider->getModelType(), $builder, $multiplier);
    }

    /**
     * @param                  $name
     * @param BuilderInterface $builder
     * @param                  $multiplier
     * @return mixed
     */
    public function create($name, BuilderInterface $builder, $multiplier)
    {
        if ($multiplier > 1) {
            $models = array();
            while ($multiplier) {
                $model = $builder->build();
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                $models[] = $model->save();
                $multiplier--;
            }
            Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
            Factory::resetMultiplier();
            return static::$fixtures[$name] = $models;
        }
        $model = $builder->build();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $model->save();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
        return static::$fixtures[$name] = $model;
    }

    /**
     *  Returns a single model previously loaded
     *
     * @param $name
     * @param $number If it has several of same type, get model with $number
     * @throws InvalidArgumentException
     * @return mixed
     */
    public function getFixture($name, $number = null)
    {
        if (!$this->hasFixture($name)) {
            throw new InvalidArgumentException("Could not find a fixture: $name");
        }
        // A number was given, and indeed the fixtures key is an array,
        // then go ahead and return the wanted number
        if ($number && is_array(static::$fixtures[$name])) {
            return static::$fixtures[$name][$number];
        }
        // If no number is specified as argument, then return the first one off
        // fixtures the array
        if (is_array(static::$fixtures[$name])) {
            return static::$fixtures[$name][0];
        }
        // Lastly, if its not an array and no number was given, just return
        // the fixture that was queried for
        return static::$fixtures[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    private function hasFixture($name) {
        return array_key_exists($name, static::$fixtures);
    }

    /**
     * Deletes all the magento fixtures
     */
    public function clear()
    {
        foreach (static::$fixtures as $model) {
            if (is_array($model)) {
                foreach ($model as $fixture) {
                    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                    $fixture->delete();
                    Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
                }
            } else {
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                $model->delete();
                Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
            }
        }
        static::$fixtures = array();
    }

    /**
     * @param $name
     * @return bool
     */
    private function hasBuilder($name)
    {
        return array_key_exists($name, $this->builders);
    }

    /**
     * @param $modelType
     * @return Builders\Address|Builders\Customer|Builders\Order|Builders\Product
     */
    private function getBuilder($modelType)
    {
        if ($this->hasBuilder($modelType)) {
            return $this->builders[$modelType];
        }

        switch ($modelType) {
            case 'admin/user':
                return $this->builders[$modelType] = new Builders\Admin($modelType);
            case 'customer/address':
                return $this->builders[$modelType] = new Builders\Address($modelType);
            case 'customer/customer':
                return $this->builders[$modelType] = new Builders\Customer($modelType);
            case 'catalog/product':
                return $this->builders[$modelType] = new Builders\Product($modelType);
            case 'sales/quote':
                return $this->builders[$modelType] = new Builders\Order($modelType);
            default :
                return $this->builders[$modelType] = new Builders\General($modelType);
        }
    }

    /**
     * @param $fixtureFile
     * @throws InvalidArgumentException
     */
    private function fixtureFileExists($fixtureFile)
    {
        if (!file_exists($fixtureFile)) {
            throw new InvalidArgumentException("The fixture file: $fixtureFile does not exist. Please check path.");
        }
    }

    /**
     * @param        $fixtureType
     * @param string $fileType
     * @return string
     */
    private function getDefaultFixtureTemplate($fixtureType, $fileType = '.yml')
    {
        $filePath = __DIR__ . '/Fixtures/';
        switch ($fixtureType) {
            case 'admin/user':
                return $filePath . 'Admin' . $fileType;
            case 'customer/address':
                return $filePath . 'Address' . $fileType;
            case 'customer/customer':
                return $filePath . 'Customer' . $fileType;
            case 'catalog/product':
                return $filePath . 'Product' . $fileType;
            case 'sales/quote':
                return $filePath . 'Order' . $fileType;
        }
    }

    /**
     *  Get the default fixture path.
     *  TODO: don't use getcwd()?
     *
     * @param      $fixtureType
     * @param null $type
     * @return string
     */
    private function getCustomFixtureTemplate($fixtureType, $type = null)
    {
        $parts = explode("/", $fixtureType);
        return implode('', array(
                getcwd(),
                static::CUSTOM_FIXTURES_DIR,
                DIRECTORY_SEPARATOR,
                $parts[1] == 'quote' ? 'order' : $parts[1],
                $type ? : '.yml'
            )
        );
    }

    /**
     * @param $fixtureType
     * @return string
     */
    private function getFallbackFixture($fixtureType)
    {
        // custom php
        if (file_exists($fixturePath = $this->getCustomFixtureTemplate($fixtureType, '.php'))) {
            return $fixturePath;
        }
        // custom yaml
        if (file_exists($fixturePath = $this->getCustomFixtureTemplate($fixtureType, '.yml'))) {
            return $fixturePath;
        }
        // default yaml
        return $this->getDefaultFixtureTemplate($fixtureType, '.yml');
    }

    /**
     * @param $dependency
     * @return string
     */
    private function getDependencyModel($dependency)
    {
        $attributesProvider = clone $this->attributesProvider;
        $attributesProvider->readFile($this->getFallbackFixture($dependency));
        $dependencyType = $attributesProvider->getModelType();
        return $this->parseDependencyModel($dependencyType);
    }

    /**
     * @param $dependencyType
     * @return string
     */
    private function parseDependencyModel($dependencyType)
    {
        preg_match("/\/(.*)/", $dependencyType, $matches);
        return ucfirst($matches[1]);
    }

    /**
     * @return array
     */
    public function getFixtures()
    {
        return static::$fixtures;
    }

}
