<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\CurrencyService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrencyService::class, function ($app) {
            return new CurrencyService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (!app()->runningInConsole()) {
                try {
                    $currencyService = app(CurrencyService::class);
                    $view->with('currencyConfig', $currencyService->getJsConfig());
                    $view->with('activeCurrencies', $currencyService->getActiveCurrencies());
                    $view->with('selectedCurrency', $currencyService->getSelectedCurrency());
                } catch (\Exception $e) {
                    $view->with('currencyConfig', [
                        'selected_currency_code' => 'USD',
                        'selected_currency_symbol' => '$',
                        'exchange_rate' => 1,
                        'decimal_places' => 2,
                        'symbol_position' => 'before',
                    ]);
                    $view->with('activeCurrencies', collect());
                    $view->with('selectedCurrency', null);
                }
            }
        });
    }
}
