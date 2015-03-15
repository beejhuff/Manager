<?php 

namespace MageTest\Manager\Attributes\Provider;

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
     * @var
     */
    private $model;

    /**
     * @return mixed
     */
    public function readAttributes()
    {
        $type = $this->getModelType();
        return $this->model[$type]['attributes'];
    }

    /**
     * @return mixed
     */
    public function getModelType()
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

        if ($this->getFileType($file) !== 'php') {
            if (!$this->loader instanceof ParseFields) {
                throw new Exception('Your loader implementation needs to be able to parse its fields.');
            }
            $this->model = $this->loader->parseFields($model);
        } else {
            $this->model = $this->loader->load($file);
        }
    }

    /**
     * @return bool
     */
    public function hasFixtureDependencies()
    {
        $type = $this->getModelType();
        return isset($this->model[$type]['depends']) && $this->model[$type]['depends'] != null;
    }

    /**
     * @return array|null
     */
    public function getFixtureDependencies()
    {
        $dependencies = isset($this->model[$this->getModelType()]['depends']) ? $this->model[$this->getModelType()]['depends'] : null;
        if (!is_array($dependencies)) {
            return array($dependencies);
        }
        return $dependencies;
    }

    /**
     * @param $file
     * @return string
     */
    private function getFileType($file)
    {
        if (strstr($file, '.yml')) {
            return 'yml';
        }

        if (strstr($file, '.xml')) {
            return 'xml';
        }
        return 'php';
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
     * @return string
     */
    private function getLoader($file)
    {
        $fileType = $this->getFileType($file);
        $loader = __NAMESPACE__ . '\\Loader\\' . ucfirst($fileType) . 'Loader';
        return new $loader;
    }

}
