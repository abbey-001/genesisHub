<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AlgoliaSearchService;
use App\Services\ProductListingService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AlgoliaSearchService::class);

        $this->app->singleton(ProductListingService::class, function ($app) {
            return new ProductListingService($app->make(AlgoliaSearchService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
