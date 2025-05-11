<?php

namespace App\Providers;

use App\Http\Controllers\ActivitiesLogController;
use App\Http\Controllers\ProductCategoryController;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\UserController;
use App\Models\ActivitiesLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOrigin;
use App\Models\Receipt;
use App\Policies\ActivityLogPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ProductCategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReceiptPolicy;
use App\Policies\UserPolicy;

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
        // Gate::define('createOffer', [OfferPolicy::class, 'create']);


        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ActivitiesLog::class, ActivityLogPolicy::class);
        Gate::policy(ProductCategory::class, ProductCategoryPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);

    }
}
