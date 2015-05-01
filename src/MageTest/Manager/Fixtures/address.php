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