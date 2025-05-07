<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * User model represents the authenticated user of the system.
 * Implements JWTSubject for API authentication.
 *
 * @documented
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Attributes that are mass assignable.
     *
     * @var array<string>
     * @documented
     */
    protected $fillable = [
        'name',
        'password',
        'status',
    ];

    /**
     * Hidden attributes from array/json serialization.
     *
     * @var array<string>
     * @documented
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Status map for readability.
     *
     * @documented
     */
    const STATUS_MAP = [
        0 => 'exists',
        1 => 'deleted',
    ];

    /**
     * Get human-readable status attribute.
     *
     * @param int $value
     * @return string
     * @documented
     */
    public function getStatusAttribute($value): string
    {
        return self::STATUS_MAP[$value] ?? 'Unknown';
    }
    // public function status(): Attribute
    //     {
    //         return Attribute::make(
    //             get: fn($value) => self::STATUS_MAP[$value] ?? 'Unknown',
    //             set: fn($value) => array_search($value, self::STATUS_MAP)
    //         );
    //     }

    /**
     * Attribute casting.
     *
     * @var array<string, string>
     * @documented
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * Get identifier for JWT.
     *
     * @return mixed
     * @documented
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Custom JWT claims.
     *
     * @return array
     * @documented
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * A user may have many products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @documented
     */
    public function product()
    {
        return $this->hasMany(Product::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
