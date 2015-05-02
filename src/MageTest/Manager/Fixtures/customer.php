<?php

return array(
    'customer/customer' => array(
        'attributes' => array(
            'firstname' => $this->faker->firstname,
            'lastname' => $this->faker->lastname,
            'email' => $this->faker->email,
            'password' => 'topsecret',
            'website_id' => 1,
            'store' => 1,
            'status' => 1,
            'country_id' => 'GB',
            'telephone' => '1234567890',
            'is_default_billing' => 1,
            'is_default_shipping' => 1,
            'save_in_address_book' => 1
        )
    )
);