<?php 

namespace MageTest\Manager\Attributes\Provider\Loader;


use Symfony\Component\Yaml\Yaml;

/**
 * Class YmlLoader
 *
 * @package MageTest\Manager\Attributes\Provider\Loader
 */
class YmlLoader implements Loader, ParseFields
{

    /**
     * @var \Symfony\Component\Yaml\Yaml
     */
    private $yaml;

    /**
     * @param Yaml $yaml
     */
    public function __construct(Yaml $yaml = null)
    {
        $this->yaml = $yaml ? : new Yaml;
    }

    /**
     * @param $file
     * @return array
     */
    public function load($file)
    {
        return $this->yaml->parse($file);
    }

    /**
     * @param $model
     * @return array
     */
    public function parseFields($model)
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
        return reset($matches);
    }

    /**
     * @param $key
     * @return mixed
     */
    private function parseDependencies($key)
    {
        preg_match("/ \((.*)\)/", $key, $matches);
        if (strstr(end($matches), ' ')) {
            return explode(" ", end($matches));
        }
        return end($matches);
    }

}
