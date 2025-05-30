<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class User
 *
 * Represents an authenticated user in the system.
 * Implements JWT authentication using JWTSubject for API authentication.
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',         // Name of the user
        'password',     // Hashed password
        'status',       // Status of the user (active/deleted)
    ];

    /**
     * The attributes that should be hidden when serialized to JSON.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',         // Hide password for security reasons
        'remember_token',   // Hide the remember token
    ];

    /**
     * Status constants to enhance readability.
     *
     * @var array<int, string>
     */
    const STATUS_MAP = [
        0 => 'موجود',   // User exists
        1 => 'محذوف',  // User is removed
    ];

    /**
     * Get and set the user status dynamically using attribute casting.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function status(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::STATUS_MAP[(int) $value] ?? 'Unknown',
            set: fn ($value) => array_search($value, self::STATUS_MAP)
        );
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed', // Secure hashing of password
    ];

    /**
     * Get the identifier for JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return custom claims for JWT authentication.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Define a one-to-many relationship where a user can have multiple products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    /**
     * Define a one-to-many relationship where a user can have multiple payments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Define a one-to-many relationship where a user can have multiple debts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function debts()
    {
        return $this->hasMany(Debt::class);
    }
}
