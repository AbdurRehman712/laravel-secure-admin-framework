<?php

namespace Modules\Shop\database\factories;

use Modules\Shop\app\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => $this->faker->unique()->numerify('ORD-####'),
            'customer_name' => $this->faker->words(2, true),
            'customer_email' => $this->faker->unique()->safeEmail(),
            'customer_phone' => $this->faker->words(2, true),
            'billing_address' => $this->faker->words(2, true),
            'subtotal' => $this->faker->randomFloat(2, 10, 1000),
            'tax_amount' => $this->faker->randomFloat(2, 1, 100),
            'shipping_amount' => $this->faker->randomFloat(2, 1, 100),
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'payment_method' => $this->faker->words(2, true),
            'notes' => $this->faker->text(),
            'shipped_at' => $this->faker->dateTime()
        ];
    }
}