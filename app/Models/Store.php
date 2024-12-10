<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Store extends Model
{
    //

    protected $guarded=[];
    public function user(): HasOne{
        return $this->hasOne(User::class);
    }
    public function products(): HasMany{
        return $this->hasMany(Product::class);
    }
    public function orders(): HasMany{
        return $this->hasMany(Order::class);
    }
}
