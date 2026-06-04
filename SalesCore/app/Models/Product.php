<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable =[
        'category_id',
        'supplier_id',
        'name',
        'sku',
        'barcode',
        'unit',
        'cost_price',
        'sale_price',
        'stock_quantity',
        'minimum_stock',
        'active',
    ];

    protected $casts =[
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'decimal:3',
        'minimum_stock' => 'decimal:3',
        'active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
