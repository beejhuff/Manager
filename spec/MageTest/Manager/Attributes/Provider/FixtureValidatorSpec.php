<?php

namespace spec\MageTest\Manager\Attributes\Provider;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FixtureValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('MageTest\Manager\Attributes\Provider\FixtureValidator');
    }

    function it_lets_the_attributes_pass_through_if_the_array_contains_1_keys()
    {
        $this->validate(['foo' => ['attributes' => ['key' => 'value']]])
             ->shouldBe(['foo' => ['attributes' => ['key' => 'value']]]);
    }

    function it_throws_an_exception_if_the_argument_is_a_string()
    {
        $model = 'foo';
        $this->shouldThrow('RuntimeException')->duringValidate($model);
    }

    function it_throws_an_exception_if_the_argument_is_an_empty_array()
    {
        $model = [];
        $this->shouldThrow('RuntimeException')->duringValidate($model);
    }

    function it_throws_an_exception_if_the_argument_is_an_object()
    {
        $model = new \stdClass;
        $this->shouldThrow('RuntimeException')->duringValidate($model);
    }

    function it_throw_an_exception_if_the_argument_is_array_with_more_than_1_key()
    {
        $model = [1,2];
        $this->shouldThrow('RuntimeException')->duringValidate($model);
    }

    function it_should_check_for_an_associative_key_for_the_attributes()
    {
        $model = ['catalog/product' => ['attributes' => ['key' => 'value']]];
        $this->validate($model)->shouldReturn($model);
    }

    function it_throws_exception_if_attributes_are_not_present()
    {
        $model = ['catalog/product' => ['foo' => ['key' => 'value']]];
        $this->shouldThrow('Exception')->duringValidate($model);
    }

    function it_throws_exception_if_the_dependencies_key_is_malformed()
    {
        $model = ['catalog/product' => [
            'bar' => 'foo',
            'attributes' => ['key' => 'value']
            ]
        ];
        $this->shouldThrow('RuntimeException')->duringValidate($model);
    }

}
