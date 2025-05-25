<?php
namespace App\Events;

use Illuminate\Support\Facades\Log;
use App\Models\FinancialTransaction;

class FinancialTransactionEdit
{

    public $financialTransaction;
    public $type;


    public function __construct(FinancialTransaction $financialTransaction, string $type='update')
    {
        Log::info("تم تشغيل الحدث لتعديل المعاملة المالية.");
        $this->type = $type;
        $this->financialTransaction = $financialTransaction;
    }
}
