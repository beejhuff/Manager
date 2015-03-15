<?php

return array(
    'sales/quote' => array(
        'depends' => array('catalog/product', 'customer/address'),
        'attributes' => array(
            'shipping_method' => 'flatrate_flatrate',
            'payment_method' => 'checkmo'
        )
    )
);