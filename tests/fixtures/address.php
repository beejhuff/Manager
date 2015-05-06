<?php

return array(
    'customer/address' => array(
        'depends' => 'customer/customer',
        'attributes' => array(
            'company' => $this->faker->company,
            'street' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'postcode' =>  '11450',
            'country' => 'Sweden',
            'country_id' => 'SE',
            'telephone' => $this->faker->phoneNumber,
            'is_default_billing' => 1,
            'is_default_shipping' => 1,
            'save_in_address_book' => 1
        )
    )
);