<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $fillable = [
        'sale_id',
        'payment_method_id',
        'payment_method_name',
        'amount',
        'installments',
        'authorization_code',
    ];

    protected $casts =[
        'amount' => 'decimal:2',
        'installments' => 'integer',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
