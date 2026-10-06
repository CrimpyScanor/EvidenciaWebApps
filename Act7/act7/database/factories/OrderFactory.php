<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'total_value' => $this->faker->numberBetween(100, 5000),
            'tax' => $this->faker->numberBetween(10, 500),
            'shipping_charges' => $this->faker->numberBetween(50, 300),
        ];
    }
}