<?php
namespace MageTest\Manager;

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
     * @var array
     */
    private $fixtures = array();

    /**
     * @var array
     */
    private $builders = array();

    /* @var \MageTest\Manager\Attributes\Provider\ProviderInterface */
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
     * @internal param $fixtureFile
     * @return mixed
     */
    public function loadFixture($fixtureType, $userFixtureFile = null, array $overrides = null)
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
                $withDependency = 'with' . $this->getFallbackModel($dependency);
                $builder->$withDependency($this->loadFixture($dependency));
            }
        }
        return $this->create($attributesProvider->getModelType(), $builder);
    }

    /**
     * @param                  $name
     * @param BuilderInterface $builder
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function create($name, BuilderInterface $builder)
    {
        $model = $builder->build();

        \Mage::app()->setCurrentStore(\Mage_Core_Model_App::ADMIN_STORE_ID);
        $model->save();
        \Mage::app()->setCurrentStore(\Mage_Core_Model_App::DISTRO_STORE_ID);

        return $this->fixtures[$name] = $model;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getFixture($name)
    {
        if (!$this->hasFixture($name)) {
            throw new \InvalidArgumentException("Could not find a fixture: $name");
        }

        return $this->fixtures[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    private function hasFixture($name) {
        return array_key_exists($name, $this->fixtures);
    }

    /**
     * Deletes all the magento fixtures
     */
    public function clear()
    {
        foreach ($this->fixtures as $model) {
            \Mage::app()->setCurrentStore(\Mage_Core_Model_App::ADMIN_STORE_ID);
            $model->delete();
            \Mage::app()->setCurrentStore(\Mage_Core_Model_App::DISTRO_STORE_ID);
        }
        $this->fixtures = array();
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
     * @throws \InvalidArgumentException
     */
    private function fixtureFileExists($fixtureFile)
    {
        if (!file_exists($fixtureFile)) {
            throw new \InvalidArgumentException("The fixture file: $fixtureFile does not exist. Please check path.");
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
                return $filePath . 'admin' . $fileType;
            case 'customer/address':
                return $filePath . 'address' . $fileType;
            case 'customer/customer':
                return $filePath . 'customer' . $fileType;
            case 'catalog/product':
                return $filePath . 'product' . $fileType;
            case 'sales/quote':
                return $filePath . 'order' . $fileType;
        }
    }

    /**
     *  Get the default fixture path
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
                getcwd() . '/tests/fixtures/',
                strtolower($parts[1]),
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
        // default php
        if (file_exists($fixturePath = $this->getDefaultFixtureTemplate($fixtureType, '.php'))) {
            return $fixturePath;
        }
        // default yaml
        return $this->getDefaultFixtureTemplate($fixtureType);
    }

    /**
     * @param $dependency
     * @return string
     */
    private function getFallbackModel($dependency)
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

}
