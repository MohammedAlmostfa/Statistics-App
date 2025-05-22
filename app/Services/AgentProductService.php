<?php

namespace App\Services;

use Exception;
use App\Models\Debt;
use App\Models\Receipt;
use App\Models\Customer;
use App\Models\ActivitiesLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use App\Http\Resources\CustomerReceiptProduct;

/**use Illuminate\Support\Facades\Auth;

 * CustomerService
 *
 * This service provides methods for managing customer records,
 * including retrieving, creating, updating, and deleting customers.
 * It also supports caching and error logging for optimized performance.
 */
class AgentProductService extends Service
{
}
