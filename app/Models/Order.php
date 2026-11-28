<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_number',
        'customer_name',
        'customer_fiscal_information',
        'invoice_number',
        'product_name',
        'status',
        'order_date',
        'order_datetime',
        'delivery_address',
        'notes',
        'purchase_required',
        'delivery_evidence',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date:Y-m-d',
            'order_datetime' => 'datetime',
            'purchase_required' => 'boolean',
        ];
    }
}
