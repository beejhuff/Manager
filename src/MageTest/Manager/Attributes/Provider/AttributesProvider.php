<?php

namespace MageTest\Manager\Attributes\Provider;

use Exception;
use MageTest\Manager\Attributes\Provider\Loader\Loader;
use MageTest\Manager\Attributes\Provider\Loader\ParseFields;

/**
 * Class AttributesProvider
 *
 * @package MageTest\Manager\Attributes\Provider
 */
class AttributesProvider implements ProviderInterface
{
    use OverrideAttributes;

    /**
     * @var Loader\Loader
     */
    private $loader;

    /**
     * @var array
     */
    private $model;

    /**
     * @var
     */
    private $validator;

    /**
     * @param FixtureValidator $validator
     */
    public function __construct(FixtureValidator $validator = null)
    {
        $this->validator = $validator ? : new FixtureValidator;
    }

    /**
     * @return mixed
     */
    public function readAttributes()
    {
        return $this->model[$this->getResourceName()]['attributes'];
    }

    /**
     * @return mixed
     */
    public function getResourceName()
    {
        return key($this->model);
    }

    /**
     * @param $file
     * @throws Exception
     * @return array
     */
    public function readFile($file)
    {
        $this->setLoader($file);
        $model = $this->loader->load($file);

        if ($this->isPhpType($file)) {
            return $this->model = $this->loader->load($file);
        }

        if (!$this->loader instanceof ParseFields) {
            throw new Exception('Your loader implementation needs to be able to parse its fields.');
        }

        $this->model = $this->loader->parseFields($model);
        $this->validator->validate($this->model);
    }

    /**
     * @return bool
     */
    public function hasFixtureDependencies()
    {
        $type = $this->getResourceName();
        return isset($this->model[$type]['depends']) && $this->model[$type]['depends'] != null;
    }

    /**
     * @return array|null
     */
    public function getFixtureDependencies()
    {
        $dependencies = isset($this->model[$this->getResourceName()]['depends'])
            ? $this->model[$this->getResourceName()]['depends']
            : null;
        if (!is_array($dependencies) && !is_null($dependencies)) {
            return [$dependencies];
        }
        return $dependencies;
    }

    /**
     * @param $file
     * @throws Exception
     * @return string
     */
    private function getFileType($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * @param $file
     */
    private function setLoader($file)
    {
        $this->loader = $this->getLoader($file);
    }

    /**
     * @param $file
     * @throws \Exception
     * @return string
     */
    private function getLoader($file)
    {
        $fileType = $this->getFileType($file);
        $loader = $this->buildLoaderClassPath($fileType);
        if (!class_exists($loader)) {
            throw new Exception('This loader does not exist: '.$loader);
        }
        $loaderInstance = new $loader;
        if (!$loaderInstance instanceof Loader) {
            throw new Exception('The loader must implement the Loader interface: '.$loader);
        }
        return $loaderInstance;
    }

    /**
     * @param $file
     * @return bool
     */
    private function isPhpType($file)
    {
        return $this->getFileType($file) == 'php';
    }

    /**
     * @param $fileType
     * @return string
     */
    private function buildLoaderClassPath($fileType)
    {
        return __NAMESPACE__ . '\\Loader\\' . ucfirst($fileType) . 'Loader';
    }

}
