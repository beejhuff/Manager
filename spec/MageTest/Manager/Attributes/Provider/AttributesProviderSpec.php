<?php

namespace spec\MageTest\Manager\Attributes\Provider;

use MageTest\Manager\Attributes\Provider\FixtureValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributesProviderSpec extends ObjectBehavior
{
    function let(FixtureValidator $fixtureValidator)
    {
        $this->beConstructedWith($fixtureValidator);
        $this->readFile(getcwd() . '/src/MageTest/Manager/Fixtures/Address.yml');
    }
    function it_should_implement_provider_interface()
    {
        $this->shouldImplement('MageTest\Manager\Attributes\Provider\ProviderInterface');
    }

    function it_should_read_yaml_file()
    {
        $this->readAttributes()->shouldBeLike(array(
                'company' => 'Session Digital',
                'street' => 'Brown Street',
                'street1' => 'Brown Street',
                'city' => 'Manchester',
                'postcode' =>  'M2 2JG',
                'region' => 'Lancashire',
                'country' => 'United Kingdom',
                'country_id' => 'GB',
                'telephone' => 1234567890,
                'is_default_billing' => 1,
                'is_default_shipping' => 1,
                'save_in_address_book' => 1
            ));
    }

    function it_should_get_the_magento_model_from_the_yaml_file()
    {
        $this->getResourceName()->shouldReturn('customer/address');
    }

    function it_should_read_any_dependencies_on_other_fixtures()
    {
        $this->getFixtureDependencies()->shouldReturn(array('customer/customer'));
    }

    function it_should_says_if_there_are_fixture_dependencies()
    {
        $this->hasFixtureDependencies()->shouldReturn(true);
    }

    function it_should_load_a_php_fixture()
    {
        $this->readFile(getcwd() . '/tests/fixtures/order.php');
        $this->getResourceName()->shouldReturn('sales/order');
        $this->hasFixtureDependencies()->shouldBe(true);
        $this->getFixtureDependencies()->shouldReturn(['sales/quote']);
    }

    function it_should_protest_if_the_loader_does_not_exist()
    {
        $this->shouldThrow('Exception')->duringReadFile(__DIR__ . '/orders.foo');
    }

}
