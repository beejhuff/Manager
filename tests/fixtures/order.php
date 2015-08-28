<?php

return array(
    'sales/order' => array(
        'depends' => array('sales/quote'),
        'attributes' => array(
            'shipping_method' => 'flatrate_flatrate',
            'payment_method' => 'checkmo'
        )
    )
);