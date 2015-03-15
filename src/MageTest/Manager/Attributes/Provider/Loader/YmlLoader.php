<?php 

namespace MageTest\Manager\Attributes\Provider\Loader;


use Symfony\Component\Yaml\Yaml;

class YmlLoader implements Loader
{

    private $yaml;

    public function __construct(Yaml $yaml = null)
    {
        $this->yaml = $yaml ? : new Yaml;
    }

    public function load($file)
    {
        return $this->yaml->parse($file);
    }
}