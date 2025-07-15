<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded=[];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total_value' => 'decimal:2'
    ];

    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = $value;
        $this->calculateTotalValue();
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value;
        $this->calculateTotalValue();
    }

    private function calculateTotalValue()
    {
        if (isset($this->attributes['quantity']) && isset($this->attributes['price'])) {
            $this->attributes['total_value'] = $this->attributes['quantity'] * $this->attributes['price'];
        }
    }
}