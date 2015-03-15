<?php 

namespace MageTest\Manager\Attributes\Provider;

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
     * @return array
     */
    public function readFile($file)
    {
        $this->setLoader($file);
        $model = $this->loader->load($file);
        if ($this->getFileType($file) == 'yml') {
            return $this->model = $this->parseFields($model);
        }

        $this->model = $this->loader->load($file);
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

    /**
     * @param $model
     * @return array
     */
    private function parseFields($model)
    {
        return array(
            $this->parseModel(key($model)) => array(
                'depends' => $this->parseDependencies(key($model)),
                'attributes' => reset($model)
            )
        );
    }

    /**
     * @param $key
     * @return mixed
     */
    private function parseModel($key)
    {
        preg_match("/[^ (]+/", $key, $matches);
        return $matches[0];
    }

    /**
     * @param $key
     * @return mixed
     */
    private function parseDependencies($key)
    {
        preg_match("/ \((.*)\)/", $key, $matches);
        return $matches[1];
    }

}
