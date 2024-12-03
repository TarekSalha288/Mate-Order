<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Store extends Model
{
    //

    protected $guarded=[];
    public function user(): HasOne{
        return $this->hasOne(User::class);
    }
}
