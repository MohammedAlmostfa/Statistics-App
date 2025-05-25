<?php

namespace App\Events;

use App\Models\FinancialTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FinancialTransactionEdit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $transaction;

    public function __construct(FinancialTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
