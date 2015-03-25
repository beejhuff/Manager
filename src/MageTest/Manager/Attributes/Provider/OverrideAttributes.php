<?php

namespace MageTest\Manager\Attributes\Provider;


/**
 * Trait OverrideAttributes
 *
 * @package spec\MageTest\Manager\Attributes\Provider
 */
trait OverrideAttributes
{

    /**
     *  Overrides previously defined attributes, and optionally adds new
     *
     * @param array $attributes
     * @param bool  $appendNew
     * @return mixed
     */
    public function overrideAttributes(array $attributes, $appendNew = true)
    {
        $type = $this->getModelType();

        foreach ($this->model[$type]['attributes'] as $key => $value) {
            if (array_key_exists($key, $attributes)) {
                $this->model[$type]['attributes'][$key] = $attributes[$key];
            }
        }
        if ($appendNew) {
            $this->appendNewAttributes($attributes);
        }
    }

    /**
     *  Append new values to the attributes array
     *
     * @param array $attributes
     */
    private function appendNewAttributes(array $attributes)
    {
        $type = $this->getModelType();
        foreach ($attributes as $key => $value) {
            if (!array_key_exists($key, $this->model[$type])) {
                $this->model[$type]['attributes'][$key] = $value;
            }
        }
    }

}
