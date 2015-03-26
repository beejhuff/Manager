<?php

namespace MageTest\Manager\Attributes\Provider;


use RuntimeException;

/**
 * Class FixtureValidator
 *
 * @package MageTest\Manager\Attributes\Provider
 */
class FixtureValidator
{
    /**
     * @param $model
     * @return mixed
     */
    public function validate($model)
    {
        if (!is_array($model)
            || count($model) !== 1
            || !$this->hasAttributesKey($model)
            || !$this->hasAtLeastOneAttribute($model)
            || $this->hasMalformedDependencyKey($model)
        ) {
            throw new RuntimeException('Malformed attributes structure there.');
        }
        return $model;
    }

    /**
     * @param $model
     * @return bool
     */
    private function hasAttributesKey($model)
    {
        return isset($model[key($model)]['attributes']);
    }

    /**
     * @param $model
     * @return bool
     */
    private function hasAtLeastOneAttribute($model)
    {
        return count($model[key($model)]['attributes']) >= 1;
    }

    /**
     * @param $model
     */
    private function hasMalformedDependencyKey($model)
    {
        foreach ($model[key($model)] as $key => $value) {
            if ($key !== 'attributes' && $key !== 'depends') {
                throw new RuntimeException('Malformed dependencies key');
            }
        }
    }

}
