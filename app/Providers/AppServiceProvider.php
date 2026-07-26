<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\Payment\Gateways\GatewayInterface;
use App\Services\Payment\Gateways\FakeGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GatewayInterface::class, function () {

            return match (config('payment.gateway')) {

                'fake' => new FakeGateway(),

                // 'sadad' => new SadadGateway(),

                default => new FakeGateway(),

            };

        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
