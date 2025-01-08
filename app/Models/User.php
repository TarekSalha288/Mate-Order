<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasOne;
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;


    public $timestamps = false;
    protected $guarded = ['status_role','code','expire_at'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'code',
        'expire_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function generateCode(){
        $this->timestamps=false;
        $this->code=rand(100000,999999);
        $this->expire_at=now()->addMinutes(5);
        $this->save();
    }
    public function store():HasOne
    {
        return $this->hasOne(Store::class);
    }
    public function addreses(): HasMany{
        return $this->hasMany(Address::class);
    }
    public function orders(): HasMany{
        return $this->hasMany(Order::class);
    }
   public function products(): BelongsToMany{
    return $this->belongsToMany(Product::class,'favorite','user_id','product_id');
   }
   public function cart():HasMany{
    return $this->hasMany(Cart::class);
   }
   public function routeNotificationForFcm() {
    return $this->fcm_token;
}
}
