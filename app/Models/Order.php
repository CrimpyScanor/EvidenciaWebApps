<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'status', 'total_value', 'tax', 'shipping_charges'];

    protected $casts = [
        'total_value' => 'integer',
        'tax' => 'integer',
        'shipping_charges' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class);
    }
}
