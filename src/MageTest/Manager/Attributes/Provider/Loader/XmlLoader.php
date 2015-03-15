<?php 

namespace MageTest\Manager\Attributes\Provider\Loader;


class XmlLoader implements Loader
{

    public function __construct($xmlParser)
    {
        $this->xmlParser = $xmlParser;
    }

    public function load($file)
    {
        // TODO: Implement load() method.
    }
}