<?php 

namespace MageTest\Manager\Attributes\Provider\Loader;


/**
 * Class XmlLoader
 *
 * @package MageTest\Manager\Attributes\Provider\Loader
 */
class XmlLoader implements Loader, ParseFields
{
    /**
     * @var
     */
    private $xmlParser;

    /**
     * @param $xmlParser
     */
    public function __construct($xmlParser)
    {
        $this->xmlParser = $xmlParser;
    }

    /**
     * @param $file
     * @return mixed|void
     */
    public function load($file)
    {
        // TODO: Implement load() method.
    }

    /**
     * @param $model
     * @return mixed
     */
    public function parseFields($model)
    {
        // TODO: Implement parseFields() method.
    }
}