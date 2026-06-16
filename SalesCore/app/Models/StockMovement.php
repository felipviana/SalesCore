<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'product_name',
        'type',
        'quantity',
        'previous_stock',
        'new_stock',
        'source_type',
        'source_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'previous_stock' => 'decimal:3',
        'new_stock' => 'decimal:3',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
