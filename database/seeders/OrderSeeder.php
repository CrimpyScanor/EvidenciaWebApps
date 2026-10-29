<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::factory()->count(50)->create()->each(function (Order $order) {
            Invoice::create([
                'invoice_number' => 'SEED-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                'raised_at' => $order->created_at ?? now(),
                'status' => 'issued',
                'total_amount' => $order->total_value + $order->tax + $order->shipping_charges,
                'order_id' => $order->id,
            ]);
        });
    }
}
