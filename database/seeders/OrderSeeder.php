<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            [
                'invoice_number' => 'INV-1001',
                'customer_number' => 'C001',
                'product_name' => 'Laptop',
                'status' => 'Processing',
                'order_date' => '2026-11-17',
                'purchase_required' => false,
            ],
            [
                'invoice_number' => 'INV-1002',
                'customer_number' => 'C001',
                'product_name' => 'Keyboard',
                'status' => 'Shipped',
                'order_date' => '2026-11-16',
                'purchase_required' => false,
            ],
            [
                'invoice_number' => 'INV-1003',
                'customer_number' => 'C002',
                'product_name' => 'Monitor',
                'status' => 'Delivered',
                'order_date' => '2026-11-15',
                'purchase_required' => false,
                'delivery_evidence' => 'EVID-1003',
            ],
            [
                'invoice_number' => 'INV-1004',
                'customer_number' => 'C003',
                'product_name' => 'Mouse',
                'status' => 'Pending',
                'order_date' => '2026-11-14',
                'purchase_required' => true,
            ],
        ];

        foreach ($orders as $order) {
            Order::updateOrCreate(
                ['invoice_number' => $order['invoice_number']],
                $order
            );
        }
    }
}
