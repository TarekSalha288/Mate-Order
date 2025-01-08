<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    //
    protected $guarded = [];
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
    public function users(): BelongsToMany{
        return $this->belongsToMany(User::class,'favorite','user_id');
    }

    public function cart()
    {
        return $this->belongsToMany(User::class, 'cart')
                    ->withPivot('total_amount', 'total_price', 'status')
                    ->withTimestamps();
    }
}
