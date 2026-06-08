<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'document',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
