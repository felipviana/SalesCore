<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'status',
        'subtotal',
        'discount',
        'addition',
        'total',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'dicount' => 'decimal:2',
        'addition' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }
}
