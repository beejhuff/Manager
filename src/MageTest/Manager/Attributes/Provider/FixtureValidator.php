<?php 

namespace MageTest\Manager\Attributes\Provider;


class FixtureValidator
{
    public function validate($attributes)
    {
//        die(var_dump($attributes));
        return $attributes;
    }
} 