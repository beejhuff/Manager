<?php
namespace MageTest\Manager;

use InvalidArgumentException;
use Mage;
use Mage_Core_Model_App;
use MageTest\Manager\Attributes\Provider\ProviderInterface;
use MageTest\Manager\Builders\BuilderInterface;
use MageTest\Manager\Builders;
use MageTest\Manager\Cache\FileFixtureStorage;

/**
 * Class FixtureManager
 *
 * @package MageTest\Manager
 */
final class FixtureManager
{
    /**
     * @var array
     */
    public static $globalFixtureRegistry = array();

    /**
     * @var array
     */
    private $fixtures = array();

    /**
     * @var array
     */
    private $builders = array();

    /**
     * @var ProviderInterface
     */
    private $attributesProvider;

    /**
     * @var
     */
    private $storage;

    private $multiplier = array();

    /**
     * @param ProviderInterface $attributesProvider
     * @param Storage           $storage
     */
    public function __construct(ProviderInterface $attributesProvider, Storage $storage = null)
    {
        $this->attributesProvider = $attributesProvider;
        $this->storage = $storage ? : new FileFixtureStorage;
    }

    /**
     * @param       $resourceName
     * @param null  $providedFixtureFile
     * @param array $overrides
     * @param       $multiplier
     * @return mixed
     */
    public function loadFixture($resourceName, $providedFixtureFile = null, array $overrides = null, $multiplier = null)
    {
        // First time we enter this method then we will specify how many models we want to make
        // and if no argument is specified then we will want to build just 1 model
        if (!$this->multiplier[$resourceName]) {
            $this->multiplier[$resourceName] = $multiplier ? : 1;
            $this->fixtures[$resourceName] = [];
        }

        // Load an appropriate fixture file
        $attributesProvider = $this->getAttributesProvider($resourceName, $providedFixtureFile);

        // ...and apply any attribute overrides + add attributes not present in the fixture file
        if ($overrides) {
            $attributesProvider->overrideAttributes($overrides);
        }

        // Load the correct builder and set attributes on that instance
        $builder = $this->prepareBuilder($attributesProvider);

        // Load any dependencies recursively
        if ($attributesProvider->hasFixtureDependencies()) {
            foreach ($attributesProvider->getFixtureDependencies() as $dependency) {
                $withDependency = 'with' . $this->getDependencyModel($dependency);
                if ($this->isLoaded($dependency)) {
                    $builder->$withDependency($this->fetchDependency($dependency));
                } else {
                    $builder->$withDependency($this->loadFixture($dependency));
                }
            }
        }
        return $this->create($attributesProvider->getResourceName(), $builder);
    }

    /**
     * @param                  $resourceName
     * @param BuilderInterface $builder
     * @return mixed
     */
    private function create($resourceName, BuilderInterface $builder)
    {
        if ($this->multiplier[$resourceName] > 1) {
            $this->invokeBuild($resourceName, $builder);
            return $this->loadFixture($resourceName);
        }
        $this->invokeBuild($resourceName, $builder);
        return $this->fixtures[$resourceName];
    }

    /**
     * @param                  $resourceName
     * @param BuilderInterface $builder
     * @return void
     */
    private function invokeBuild($resourceName, BuilderInterface $builder)
    {
        $model = $builder->build();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $this->register($resourceName, $this->saveModel($model));
        Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
        $this->multiplier[$resourceName]--;
    }

    /**
     * @param $resourceName
     * @param $model
     * @return void
     */
    private function register($resourceName, $model)
    {
        $this->fixtures[$resourceName][] = $model;
        static::$globalFixtureRegistry[] = $model;
    }

    /**
     *  Returns a single model previously loaded
     *
     * @param $resourceName
     * @param $number If it has several of same type, get model with $number
     * @throws InvalidArgumentException
     * @return mixed
     */
    public function getFixture($resourceName, $number = null)
    {
        if (!$this->isLoaded($resourceName)) {
            throw new InvalidArgumentException("Could not find a fixture: $resourceName");
        }
        // A number was given, and indeed the fixtures key is an array,
        // then go ahead and return the wanted number
        if ($number && is_array($this->fixtures[$resourceName])) {
            return $this->fixtures[$resourceName][$number];
        }
        // If no number is specified as argument, then return the last one off
        // fixtures the array
        if (is_array($this->fixtures[$resourceName])) {
            return end($this->fixtures[$resourceName]);
        }
        // Lastly, if its not an array and no number was given, just return
        // the fixture that was queried for
        return $this->fixtures[$resourceName];
    }

    /**
     * @return array
     */
    public function getFixtures()
    {
        return static::$globalFixtureRegistry;
    }

    /**
     * Deletes all the magento fixtures
     */
    public function clear()
    {
        foreach (static::$globalFixtureRegistry as $model) {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $model->delete();
            Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
        }
        static::$globalFixtureRegistry = array();
        $this->storage->truncate();
    }


    /**
     *  Clean db
     */
    public function prepareDb()
    {
        if ($this->storage->hasData()) {
            foreach ($this->storage->getAllIdentifiers() as $model) {
                $model->delete();
            }
            $this->storage->truncate();
        }
    }

    /**
     * @param $resourceName
     * @return bool
     */
    private function isLoaded($resourceName)
    {
        return array_key_exists($resourceName, $this->fixtures);
    }

    /**
     * @param $resourceName
     * @return bool
     */
    private function hasBuilder($resourceName)
    {
        return array_key_exists($resourceName, $this->builders);
    }

    /**
     * @param $modelType
     * @return Builders\Address|Builders\Admin|Builders\Customer|Builders\General|Builders\Order|Builders\Product
     */
    private function getBuilder($modelType)
    {
        switch ($modelType) {
            case 'admin/user':
                return $this->builders[$modelType] = new Builders\Admin($modelType, $this->storage);
            case 'customer/address':
                return $this->builders[$modelType] = new Builders\Address($modelType, $this->storage);
            case 'customer/customer':
                return $this->builders[$modelType] = new Builders\Customer($modelType, $this->storage);
            case 'catalog/product':
                return $this->builders[$modelType] = new Builders\Product($modelType, $this->storage);
            case 'catalog/category':
                return $this->builders[$modelType] = new Builders\Category($modelType, $this->storage);
            case 'sales/quote':
                return $this->builders[$modelType] = new Builders\Order($modelType, $this->storage);
            default:
                return $this->builders[$modelType] = new Builders\General($modelType, $this->storage);
        }
    }

    /**
     * @param $resourceName
     * @return string
     * @throws Exception
     */
    private function loadFixtureFile($resourceName)
    {
        foreach (FixtureFallback::locationSequence() as $directory) {
            foreach (FixtureFallback::$sequence as $type) {
                if (file_exists($fixture = $directory . DIRECTORY_SEPARATOR . FixtureFallback::getFileName($resourceName, $type))) {
                    return $fixture;
                }
            }
        }
        throw new Exception('No matching fixture file was found.');
    }

    /**
     * @param $resourceName
     * @return string
     */
    private function getDependencyModel($resourceName)
    {
        $attributesProvider = clone $this->attributesProvider;
        $attributesProvider->readFile($this->loadFixtureFile($resourceName));
        $dependencyType = $attributesProvider->getResourceName();
        return $this->parseDependencyModel($dependencyType);
    }

    /**
     * @param $resourceName
     * @return string
     */
    private function parseDependencyModel($resourceName)
    {
        preg_match("/\/(.*)/", $resourceName, $matches);
        return ucfirst(end($matches));
    }

    /**
     * @param \Mage_Core_Model_Abstract $model
     * @return \Mage_Core_Model_Abstract
     */
    private function saveModel(\Mage_Core_Model_Abstract $model)
    {
        $model->getResource()->save($model);
        $this->storage->persistIdentifier($model);
        return $model;
    }

    /**
     * @param $resourceName
     * @param $providedFixtureFile
     * @return ProviderInterface
     * @throws Exception
     */
    private function getAttributesProvider($resourceName, $providedFixtureFile)
    {
        $attributesProvider = clone $this->attributesProvider;

        // Fetch a given fixture file
        if ($providedFixtureFile && file_exists($providedFixtureFile)) {
            $attributesProvider->readFile($providedFixtureFile);
        } else {
            // Fall back to a custom default, or to a default default
            $attributesProvider->readFile($this->loadFixtureFile($resourceName));
        }
        return $attributesProvider;
    }

    /**
     * @param $attributesProvider
     * @return Builders\Address|Builders\Admin|Builders\Customer|Builders\General|Builders\Order|Builders\Product
     */
    private function prepareBuilder($attributesProvider)
    {
        // Fetch a matching builder instance
        $builder = $this->getBuilder($attributesProvider->getResourceName());
        // Set the attributes for the builder to construct a model with
        $builder->setAttributes($attributesProvider->readAttributes());
        return $builder;
    }

    /**
     * @param $resourceName
     * @return mixed
     */
    private function fetchDependency($resourceName)
    {
        if (is_array($this->fixtures[$resourceName])) {
            return end($this->fixtures[$resourceName]);
        }
        return $this->fixtures[$resourceName];
    }

    /**
     * @param $model
     * @return $this
     */
    public function setFixtureDependency($model)
    {
        if ($model instanceof \Mage_Core_Model_Abstract) {
            $this->fixtures[$model->getResourceName()][] = $model;
        }
        return $this;
    }

    public function setMultiplierId($model)
    {
        $this->multiplier[$model] = null;
        return $this;
    }

}
