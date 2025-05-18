<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentDebt extends Model
{
    protected $fillable =[
        'amount',
        'debt_id',
        'user_id',
        'installment_date',

    ];



    protected $casts=[
        'amount'=>'integer',
        'debt_id'=>'integer',
        'user_id'=>'integer',
        'installment_date'=>'date',
    ];


    public function user()
    {
        return $this->hasOne(User::class);
    }
    /**
     * Relationship: A Receipt can have many ActivitiesLog entries.
     *
     * This function defines a polymorphic relationship between the `Receipt` model and the `ActivitiesLog` model.
     * It allows the receipt to be associated with multiple activity logs, which can store details of actions taken on the receipt.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activities()
    {
        return $this->morphMany(ActivitiesLog::class, 'type');
    }
}
