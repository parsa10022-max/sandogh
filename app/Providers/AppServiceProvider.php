<?php

namespace App\Providers;

use App\Services\Payment\Gateways\FakeGateway;
use App\Services\Payment\Gateways\GatewayInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use LogicException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GatewayInterface::class, function () {
            $gateway = config('payment.gateway');

            if ($gateway === 'fake') {
                if (! app()->environment(['local', 'testing'])) {
                    throw new LogicException(
                        'Fake payment gateway is only allowed in local and testing environments.'
                    );
                }

                return new FakeGateway();
            }

            throw new LogicException(
                'Payment gateway is not implemented or configured correctly.'
            );
        });
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
