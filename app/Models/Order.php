<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $guarded=[];
    public function store():HasOne{
    return $this->hasOne(Store::class);
    }
    public function user():HasOne{
        return $this->hasOne(user::class);
        }
}
