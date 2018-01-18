<?php

use Faker\Generator as Faker;

$factory->define(App\FakeEc2::class, function (Faker $faker) {
    return [
          'nickname'    => $faker->unique()->name;
          'instance_id' => 'i-dev1',
          'description' => 'ダミー#1',
          'terminable'  => true,
          'stop_at'     => '14:00',
          'private_ip'  => $faker->ipv4,
          'state'       => 'stopped',
    ];
});
