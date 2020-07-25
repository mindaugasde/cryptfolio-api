<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Asset;
use Faker\Generator as Faker;

$factory->define(Asset::class, function (Faker $faker) {
    return [
        'user_id' => 1,
        'label' => 'binance',
        'quantity' => 10.5,
        'currency' => 'BTC',
        'exchange_rate' => 9625.79,
        'trade_price' => 9636.29,
        'trade_date' => $faker->dateTime,
    ];
});
