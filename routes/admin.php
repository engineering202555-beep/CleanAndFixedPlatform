<?php


use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\AreaStatsController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlockedProviderController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerGrowthStatsController;
use App\Http\Controllers\Admin\CustomerManageController;
use App\Http\Controllers\Admin\PriceIntelligenceController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ServiceProviderController;
use App\Http\Controllers\Admin\ServiceProviderRequestsController;
use App\Http\Controllers\Admin\ServiceProvidersManageController;
use App\Http\Controllers\Admin\ServiceProviderSubscriptionsController;
use App\Http\Controllers\Admin\ServiceRequestGrowthStatsController;
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

    });



