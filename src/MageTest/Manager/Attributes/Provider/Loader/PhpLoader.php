<?php 

namespace MageTest\Manager\Attributes\Provider\Loader;

use Faker\Factory as Faker;

/**
 * Class PhpLoader
 *
 * @package MageTest\Manager\Attributes\Provider\Loader
 */
class PhpLoader implements Loader
{

    /**
     * @var \Faker\Faker
     */
    private $faker;

    /**
     * @param Faker $faker
     */
    public function __construct(Faker $faker = null)
    {
        $this->faker = $faker ? : Faker::create();
    }

    /**
     * @param $file
     * @return mixed
     */
    public function load($file)
    {
        return require("$file");
    }

}
