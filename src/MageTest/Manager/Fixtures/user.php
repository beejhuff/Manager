<?php

return array(
    'admin/user' => array(
        'attributes' => array(
            'username' => $this->faker->username,
            'firstname' => $this->faker->firstNameFemale,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => 'testadmin123'
        )
    )
);