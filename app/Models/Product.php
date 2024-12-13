<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    //
    protected $guarded = [];
    public function store(): HasOne
    {
        return $this->hasOne(Store::class,'id');
    }
    public function users(): BelongsToMany{
        return $this->belongsToMany(User::class,'favorite','user_id');
    }
}
