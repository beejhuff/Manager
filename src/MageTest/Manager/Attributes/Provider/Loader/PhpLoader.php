<?php 

namespace MageTest\Manager\Attributes\Provider\Loader;

use Faker\Factory as Faker;

class PhpLoader implements Loader
{

    /**
     * @var \Faker\Factory
     */
    private $faker;

    public function __construct(Faker $faker = null)
    {
        $this->faker = $faker ? : Faker::create();
    }

    public function load($file)
    {
        return require("$file");
    }
}