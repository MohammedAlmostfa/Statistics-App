<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ActivitiesLog extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'description',
    'type_id',
    'type_type',
];

    public function type()
    {
        return $this->morphTo();
    }
    protected $casts = [
        'activity_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
