<?php 

namespace MageTest\Manager\Builders;


class ConfigurableProduct extends AbstractBuilder implements BuilderInterface
{
    public function build()
    {
        $this->model->setTypeId();
    }
}