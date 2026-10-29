<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_number', 'raised_at', 'status', 'total_amount', 'order_id'];

    protected $casts = [
        'raised_at' => 'datetime',
        'total_amount' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
