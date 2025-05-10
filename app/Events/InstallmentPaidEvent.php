<?php

namespace App\Events;

use App\Models\Installment;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class InstallmentPaidEvent
{
    use Dispatchable, SerializesModels, SerializesModels;

    public Installment $installment;

    public function __construct(Installment $installment)
    {

        $this->installment = $installment;
    }
}
