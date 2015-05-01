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
     * @var
     */
    private $storage;

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
     * @param       $fixtureType
     * @param null  $userFixtureFile
     * @param array $overrides
     * @param       $multiplier
     * @return mixed
     */
    public function loadFixture($fixtureType, $userFixtureFile = null, array $overrides = null, $multiplier = null)
    {
        $attributesProvider = clone $this->attributesProvider;

        // Fetch a given fixture file
        if ($userFixtureFile) {
            $this->fixtureFileExists($userFixtureFile);
            $attributesProvider->readFile($userFixtureFile);
        } else {
            // Fall back to a custom default, or to a default default
            $attributesProvider->readFile($this->getFallbackFixture($fixtureType));
        }

        // ...and override attributes + add non-existing ones too
        if ($overrides) {
            $attributesProvider->overrideAttributes($overrides);
        }

        // Fetch a matching builder instance
        $builder = $this->getBuilder($attributesProvider->getModelType());

        // Set the attributes for the builder to construct a model with
        $builder->setAttributes($attributesProvider->readAttributes());

        // Load any dependencies recursively
        if ($attributesProvider->hasFixtureDependencies()) {
            foreach ($attributesProvider->getFixtureDependencies() as $dependency) {
                $withDependency = 'with' . $this->getDependencyModel($dependency);
                if ($this->hasFixture($dependency)) {
                    // When building models that has a dependency, it is nice to be able to
                    // first build the dependency and then the dependant model. The trick is
                    // to check if there is a model already built and if so, reuse that guy
                    // when building the dependant class
                    if (is_array(self::$fixtures[$dependency])) {
                        // If they key holds an array of models, then just use the first one
                        $builder->$withDependency(reset(self::$fixtures[$dependency]));
                    } else {
                        $builder->$withDependency(self::$fixtures[$dependency]);
                    }
                } else {
                    // Otherwise, just go ahead and create a new model
                    $builder->$withDependency($this->loadFixture($dependency));
                }
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
    private function create($name, BuilderInterface $builder, $multiplier)
    {
        if ($multiplier > 1) {
            $models = array();
            while ($multiplier) {
                $model = $builder->build();
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                $models[] = $this->saveModel($model);
                $multiplier--;
            }
            Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
            Factory::resetMultiplier();

            return static::$fixtures[$name] = $models;
        }
        $model = $builder->build();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $this->saveModel($model);
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
        $this->storage->truncate();
    }

    /**
     * @return array
     */
    public function getFixtures()
    {
        return static::$fixtures;
    }

    /**
     * @param $name
     * @return bool
     */
    private function hasFixture($name)
    {
        return array_key_exists($name, static::$fixtures);
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
     * @return Builders\Address|Builders\Admin|Builders\Customer|Builders\General|Builders\Order|Builders\Product
     */
    private function getBuilder($modelType)
    {
        if ($this->hasBuilder($modelType)) {
            return $this->builders[$modelType];
        }

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
                if (file_exists($adminFixture = $filePath . 'admin.php')) {
                    return $adminFixture;
                }
                return $filePath . 'Admin' . $fileType;
            case 'customer/address':
                if (file_exists($addressFixture = $filePath . 'address.php')) {
                    return $addressFixture;
                }
                return $filePath . 'Address' . $fileType;
            case 'customer/customer':
                if (file_exists($customerFixture = $filePath . 'customer.php')) {
                    return $customerFixture;
                }
                return $filePath . 'Customer' . $fileType;
            case 'catalog/product':
                if (file_exists($productFixture = $filePath . 'product.php')) {
                    return $productFixture;
                }
                return $filePath . 'Product' . $fileType;
            case 'sales/quote':
                if (file_exists($orderFixture = $filePath . 'order.php')) {
                    return $orderFixture;
                }
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
        if (!is_string($type) and !is_null($type)) {
            throw new InvalidArgumentException(
                sprintf('2nd argument must have string type. %s given', [var_dump($type)])
            );
        }
        $parts = explode("/", $fixtureType);
        return implode(
            '',
            array(
                getcwd(),
                static::CUSTOM_FIXTURES_DIR,
                DIRECTORY_SEPARATOR,
                end($parts) == 'quote' ? 'order' : end($parts),
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
        foreach (FixtureFallback::$sequence as $type) {
            if (file_exists($fixture = $this->getCustomFixtureTemplate($fixtureType, $type))) {
                return $fixture;
            }
        }
        return $this->getDefaultFixtureTemplate($fixtureType);
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
        return ucfirst(end($matches));
    }

    /**
     * @param $model
     * @return mixed
     */
    private function saveModel($model)
    {
	    $model->getResource()->save($model);
        $this->storage->persistIdentifier($model);
	    return $model;
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

}
