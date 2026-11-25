<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_number',
        'invoice_number',
        'product_name',
        'status',
        'order_date',
        'purchase_required',
        'delivery_evidence',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date:Y-m-d',
            'purchase_required' => 'boolean',
        ];
    }
}
