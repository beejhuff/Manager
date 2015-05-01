<?php

return array(
    'catalog/category' => array(
        'attributes' => array(
            'name' => $this->faker->name,
            'url_key' => $this->faker->slug,
            'store_id' => Mage_Core_Model_App::ADMIN_STORE_ID,
            'path' => '1/2',
            'is_active' =>  '1',
            'is_anchor' => '1',
            'include_in_menu' => '1',
            'display_mode' => 'PRODUCTS'
        )
    )
);