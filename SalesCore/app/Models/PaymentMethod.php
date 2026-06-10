<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function salePayments()
    {
        return $this->hasMany(SalePayment::class);
    }
}
