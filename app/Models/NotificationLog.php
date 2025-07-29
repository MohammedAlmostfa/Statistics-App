<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'customer_id', 'message', 'status', 'response'
    ];

    // علاقة بالسجل المرتبط بالعميل
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Accessor لعرض حالة مفهومة (نص عربي بدل true/false)
    public function getStatusTextAttribute()
    {
        return $this->status ? 'تم الارسال' : 'فشل الارسال';
    }
}
