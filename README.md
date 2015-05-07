Manager
===

[![Build Status](https://travis-ci.org/dwickstrom/Manager.svg?branch=v1.0.2)](https://travis-ci.org/dwickstrom/Manager)

*This is a fork off MageTest/Manager, on top of which more advanced features has been added.*

Manager is a PHP library that manages test fixtures for a Magento 1.9.* application. Inspired by Ruby's Factory Girl and others.

Fixtures
---
Manager use API is a factory class with a number of optional arguments as well as the mandatory "make"-method that ignites the building of a Mage_Core_Model_Abstract object.

Manager comes with some default fixture templates defined in PHP. You can define your own default templates for your project, as well as override your own defaults temporary if you want to.

	Factory::make('catalog/product', null, getcwd() . '/foo/fixture.yml');

Regardless of the fixture template used, you can always override that template like so:

	Factory::make('customer/customer', [
		'firstname' => 'Foo'
		'lastname' => 'Bar'
	]);

### Fixture template formats
You can define your custom fixture templates in either PHP or in Yaml. The Yaml template should conform to this format:

	customer/customer:
    	firstname: test
    	lastname: test
	    email: customer@example.com
    	password: 123123pass
	    website_id: 1
    	store: 1
	    status: 1

...and if your fixture depends on other models to be generated

	customer/address (customer/customer):
		company: Session Digital
    	street: Brown Street
    	street1: Brown Street
    	city: Manchester
    	postcode:  M2 2JG
    	region: Lancashire
    	country: United Kingdom
    	country_id: GB
    	telephone: 1234567890
    	is_default_billing: 1
    	is_default_shipping: 1
    	save_in_address_book: 1

...where the 'customer/address' resource name is the dependency in this case.

The PHP format should must conform to this standard:

	<?php

	return array(
    	'customer/address' => array(
        	'depends' => 'customer/customer',
	        'attributes' => array(
            	'company' => 'Karlsson & Lord',
	            'street' => 'Swedenborgsgatan 1',
    	        'city' => 'Stockholm',
        	    'postcode' =>  '11450',
            	'region' => 'Södertörn',
	            'country' => 'Sweden',
    	        'country_id' => 'SE',
        	    'telephone' => '1234567890',
            	'is_default_billing' => 1,
	            'is_default_shipping' => 1,
    	        'save_in_address_book' => 1
	        )
    	)
	);

Usage
---
Use this module together with your favourite testing framework; Behat, PHPUnit, Codeception and others. There are plenty of different approaches on how to set these of tests up. Here is one:

	public function testSomething()
	{
		$product = Factory::make('catalog/product, ['name' => 'foobar]);

		$foo = Mage::getModel('catalog/product')->load($product->getId());

		$this->assertEquals('foobar', $foo->getName());

	}

	public function tearDown()
	{
		Factory::clear();
	}

When creating your models they are all stored in a global registry for the length of the application request flow. This means that you can within that same application request flow, easily clear your database.

	Factory::clear();

On top of that, if you require, there is also the possibility to clear the models created in your previous application request flow.

	Factory::prepareDb();

This is possible because all model's identifiers are store in a file cache, that help remembering what fixtures where created before.

This method can, mostly for debugging purposes, during the "setup"-stage of your test.

	public function setUp()
	{
		Factory::prepareDb();
	}

This will mostly be useful when in development mode. But also if you are specifying a key that cannot be duplicated in your database.

Imagine a scenario when your test creates a model with a specific unique key, that cannot be duplicated, and for some reason your test fails. Now, if you restart your test after making some change to your code, then the Factory won't be allowed to create your required test fixture due to key duplication. In this kind of scenario it is very helpful to be able to clear the db *after* the test fails and *before* it runs the next time.

Multiple models
---
It's very easy to create an array of similar models:

	$orders = Factory::times(10)->make('sales/quote');

	print count($orders) // 10

The $orders variables will now contain an array of 10 orders.

Managing dependencies
---
A common scneario is when you need to create a model that depends on the presence of another type model, and on top of that, the other model needs to have certain attributes set. This can be achieved like this:

	$customer = Factory::make('customer/customer', [
		'firstname' => 'Foe'
	]);

	$address = Factory::with($customer)->make('customer/address');

...and so now this the address created belongs to the customer that was created before.

Another example of the type of scenario is when you want to create an order like this:

	$products = Factory::times(3)->make('catalog/product');

	$order = Factory::with($products)->make('sales/quote');

The $order variable will no contain 1 order with 3 order rows. This is possible because the module is intelligent enough to know which types of models that can have multiple relations to different dependencies of the same type.

Roadmap
---
- Add support for Configurable products, Bundled products
- Add support for specifying additional arguments when setting dependencies on a builder
- JSON, XML attribute providers.

Contributors
---
Authors: https://github.com/dwickstrom/Manager/contributors
