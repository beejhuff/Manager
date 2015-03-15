<?php

namespace MageTest\Manager\Attributes\Provider\Loader;


/**
 * Interface ParseFields
 *
 * @package MageTest\Manager\Attributes\Provider\Loader
 */
interface ParseFields
{
    /**
     * @param $model
     * @return mixed
     */
    public function parseFields($model);
} 