<?php

namespace App\Providers;

use App\Models\Trade;
use App\Models\Asset;
use App\Policies\TradePolicy;
use App\Policies\AssetPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Trade::class => TradePolicy::class,
        Asset::class => AssetPolicy::class,
    ];

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
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
