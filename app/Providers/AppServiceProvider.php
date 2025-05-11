<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Customer;
use App\Policies\UserPolicy;
use App\Models\ActivitiesLog;
use App\Models\ProductOrigin;
use App\Models\ProductCategory;
use App\Policies\ProductPolicy;
use App\Policies\ReceiptPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\WhatsAppPolicy;
use App\Policies\ActivityLogPolicy;
use Illuminate\Support\Facades\Gate;
use App\Policies\FinacialReportPolicy;
use App\Policies\ProductCategoryPolicy;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivitiesLogController;
use App\Http\Controllers\ProductCategoryController;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gate::define('createMeal', [MealPolicy::class, 'create']);


        Gate::define('GetWhatssapMessage', fn ($user) => app(WhatsAppPolicy::class)->GetWhatssapMessage($user));


        Gate::define('GetFinacialReport', fn ($user) => app(FinacialReportPolicy::class)->GetFinacialReport($user));


        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ActivitiesLog::class, ActivityLogPolicy::class);
        Gate::policy(ProductCategory::class, ProductCategoryPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);

    }
}
