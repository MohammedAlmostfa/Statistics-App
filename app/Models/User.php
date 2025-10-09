<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class User
 *
 * Represents an authenticated user in the system.
 * Implements JWT authentication using JWTSubject for API authentication.
 *
 * @property int $id
 * @property string $name
 * @property string $password
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * Relationships:
 * @property \Illuminate\Database\Eloquent\Collection $products
 * @property \Illuminate\Database\Eloquent\Collection $financialTransactions
 * @property \Illuminate\Database\Eloquent\Collection $payments
 * @property \Illuminate\Database\Eloquent\Collection $debts
 * @property \Illuminate\Database\Eloquent\Collection $installmentPayments
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'password',
        'status',
    ];

    /**
     * Attributes hidden for JSON serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Status mapping constants.
     *
     * @var array<int, string>
     */
    const STATUS_MAP = [
        0 => 'موجود',
        1 => 'محذوف',
    ];

    /**
     * Casts for attributes.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed', // Securely hash passwords
    ];

    /**
     * Attribute accessor & mutator for status.
     * Returns the human-readable status when getting.
     * Stores the numeric value when setting.
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
     * Get the identifier that will be stored in JWT.
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
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * One-to-many relationship: user has many products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * One-to-many relationship: user has many financial transactions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    /**
     * One-to-many relationship: user has many payments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * One-to-many relationship: user has many debts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    /**
     * One-to-many relationship: user has many installment payments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function installmentPayments()
    {
        return $this->hasMany(InstallmentPayment::class);
    }
}
