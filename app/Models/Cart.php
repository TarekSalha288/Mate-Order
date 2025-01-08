<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function product(): BelongsTo{
        return $this->belongsTo(Product::class);
    }
    public function order(): BelongsTo{
        return $this->belongsTo(Order::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
