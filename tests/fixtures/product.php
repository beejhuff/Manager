<?php

return array(
    'catalog/product' => array(
        'attributes' => array(
            'sku' => $this->faker->ean,
            'attribute_set_id' => 1,
            'name' => 'foobar',
            'weight' => 2,
            'price' => 100,
            'description' => $this->faker->text,
            'short_description' => $this->faker->text,
            'tax_class_id' => 1,
            'type_id' => 'simple',
            'visibility' => 4,
            'status' => 1,
            'stock_data' => array( 'is_in_stock' => 1, 'qty' => 100 ),
            'website_ids' => array(1)
        )
    )
);