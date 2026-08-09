<?php


use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\AreaStatsController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlockedProviderController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ComplaintController;
use App\Http\Controllers\Admin\ComplaintStatsController;
use App\Http\Controllers\Admin\ComplaintStatusController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerGrowthStatsController;
use App\Http\Controllers\Admin\CustomerManageController;
use App\Http\Controllers\Admin\PriceIntelligenceController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceCategoryStatsController;
use App\Http\Controllers\Admin\ServiceProviderController;
use App\Http\Controllers\Admin\ServiceProviderRequestsController;
use App\Http\Controllers\Admin\ServiceProvidersManageController;
use App\Http\Controllers\Admin\ServiceProviderSubscriptionsController;
use App\Http\Controllers\Admin\ServiceRequestGrowthStatsController;
use App\Http\Controllers\Admin\SubscriptionActivationController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SubscriptionProviderController;
use App\Http\Controllers\Admin\SubscriptionRevenueStatsController;
use App\Http\Resources\Admin\ServiceProviderBlockedResource;
use Illuminate\Support\Facades\Route;


    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:admin',
    ])->group(function () {

        Route::post('/change-password', [AuthController::class, 'changePassword']);

    });

    Route::middleware(['auth:sanctum', 'role:admin', 'force.password.change',
    ])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
/////Service Providers Section:
        Route::get('/service-providers', [ServiceProviderController::class, 'getApprovedProviders']);
        Route::get('/service-provider/{serviceProvider}', [ServiceProviderController::class, 'getInfoProvider']);
        Route::get('/service-providers-pending', [ServiceProviderController::class, 'getPendingProviders']);
        Route::get('/service-providers-rejected', [ServiceProviderController::class, 'getRejectedProviders']);
        Route::get('/service-providers-filter', [ServiceProviderController::class, 'getApprovedProvidersFilter']);
        Route::get('/service-providers-subscriptions-breakdown', [ServiceProviderSubscriptionsController::class , 'getProvidersSubscriptionsDetails']);
        Route::get('/most-active', [ServiceProviderController::class, 'mostActive']);
        Route::get('/most-complained', [ServiceProviderController::class, 'mostComplained']);

        Route::patch('/approval/{serviceProvider}', [ServiceProviderRequestsController::class, 'approval']);
        Route::patch('/reconsideration/{serviceProvider}', [ServiceProviderRequestsController::class, 'reconsider']);

        Route::delete('/delete-service-provider/{serviceProvider}', [ServiceProvidersManageController::class, 'deleteServiceProvider']);
        Route::patch('/block/{serviceProvider}', [ServiceProvidersManageController::class, 'block']);
        Route::patch('/unblock/{serviceProvider}', [ServiceProvidersManageController::class, 'unblock']);
        Route::get('/service-providers-blocked', [ServiceProviderController::class, 'getBlockedProviders']);
        Route::patch('/service-providers-complimentary-month/{serviceProvider}', [ServiceProviderSubscriptionsController::class,'grantComplimentarySubscription']);
////////Customers Section:
        Route::get('/customers', [CustomerController::class, 'getCustomersByFilter']);
        Route::get('/customers-blocked', [CustomerController::class, 'getCustomersBlocked']);

        Route::delete('/delete-customer/{customer}', [CustomerManageController::class, 'destroy']);
        Route::patch('/block-customer/{customer}', [CustomerManageController::class, 'block']);
        Route::patch('/unblock-customer/{customer}', [CustomerManageController::class, 'unblock']);
        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::get('/blocked-providers-by-customers', [BlockedProviderController::class, 'index'])->name('customers.blocked-providers.index');
        Route::get('/stats-customers-growth', CustomerGrowthStatsController::class)->name('stats.customers-growth');
        Route::get('/stats-service-requests-growth', ServiceRequestGrowthStatsController::class)->name('stats.service-requests-growth');

        /////Area Service Section :
        /// المدن
        Route::get('cities', [CityController::class, 'index'])->name('cities.index');
        Route::get('cities/dropdown', [CityController::class, 'citiesDropdown'])->name('cities.dropdown');
        Route::post('cities', [CityController::class, 'store'])->name('cities.store');
        Route::patch('cities/{city}', [CityController::class, 'renameCity'])->name('cities.rename');
        Route::delete('cities/{city}', [CityController::class, 'destroyCity'])->name('cities.destroy');

        // المناطق
        Route::post('areas', [AreaController::class, 'store'])->name('areas.store');
        Route::patch('areas/{serviceArea}', [AreaController::class, 'update'])->name('areas.update');
        Route::delete('areas/{serviceArea}', [AreaController::class, 'destroy'])->name('areas.destroy');

        ////احصائيات
        Route::prefix('service-areas/stats')->name('service-areas.stats.')->group(function () {

                Route::get('hot-areas', [AreaStatsController::class, 'hotAreas'])->name('hot-areas');
                Route::get('density', [AreaStatsController::class, 'density'])->name('density');
                Route::get('complaints', [AreaStatsController::class, 'complaints'])->name('complaints');
                Route::get('provider-distribution', [AreaStatsController::class, 'providerDistribution'])->name('provider-distribution');
                Route::get('supply-demand', [AreaStatsController::class, 'supplyDemand'])->name('supply-demand');
                Route::get('geographic-growth', [AreaStatsController::class, 'geographicGrowth'])->name('geographic-growth');

                Route::get('price-comparison', [PriceIntelligenceController::class, 'compare'])->name('price-comparison');
                Route::get('price-trend', [PriceIntelligenceController::class, 'monthlyTrend'])->name('price-trend');

            });

        /////Service areas section:
        Route::prefix('service-categories')->name('service-categories.')->group(function () {

            Route::get('/', [ServiceCategoryController::class, 'index'])->name('index');
            Route::post('/', [ServiceCategoryController::class, 'store'])->name('store');
            Route::patch('{serviceCategory}', [ServiceCategoryController::class, 'update'])->name('update');
            Route::delete('{serviceCategory}', [ServiceCategoryController::class, 'destroy'])->name('destroy');

            Route::prefix('stats')->name('stats.')->group(function () {
                Route::get('most-requested', [ServiceCategoryStatsController::class, 'mostRequested'])->name('most-requested');
                Route::get('provider-distribution', [ServiceCategoryStatsController::class, 'providerDistribution'])->name('provider-distribution');
            });

        });

        // خطط الاشتراك
        Route::prefix('subscription-plans')->name('subscription-plans.')->group(function () {
            Route::get('/', [SubscriptionPlanController::class, 'index'])->name('index');
            Route::get('{subscription}', [SubscriptionPlanController::class, 'show'])->name('show');
            Route::post('/', [SubscriptionPlanController::class, 'store'])->name('store');
            Route::patch('{subscription}', [SubscriptionPlanController::class, 'update'])->name('update');
            Route::delete('{subscription}', [SubscriptionPlanController::class, 'destroy'])->name('destroy');
        });

        // اشتراكات مقدمي الخدمة
        Route::prefix('provider-subscriptions')->name('provider-subscriptions.')->group(function () {
            Route::get('/', [SubscriptionProviderController::class, 'index'])->name('index');
            Route::get('{subscriptionProvider}', [SubscriptionProviderController::class, 'show'])->name('show');
            Route::patch('activate/{subscriptionProvider}', SubscriptionActivationController::class)->name('activate');
        });
//ارباح المنصة :
        Route::get('stats/subscriptions-revenue', SubscriptionRevenueStatsController::class)->name('stats.subscriptions-revenue');
/////complaints:
        Route::prefix('complaints')->name('complaints.')->group(function () {
            // ثابت (stats) لازم قبل {complaint} الديناميكي، تفادياً لأي التباس مستقبلي
            Route::get('stats', ComplaintStatsController::class)->name('stats');

            Route::get('/', [ComplaintController::class, 'index'])->name('index');
            Route::get('{complaint}', [ComplaintController::class, 'show'])->name('show');
            Route::patch('/status/{complaint}', ComplaintStatusController::class)->name('status');
        });

    });



