<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable =[
        'name',
        'document',
        'phone',
        'email',
        'active',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
