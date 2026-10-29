<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['executed_at', 'status', 'payment_mode', 'transaction_reference', 'order_id'];

    protected $casts = ['executed_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
