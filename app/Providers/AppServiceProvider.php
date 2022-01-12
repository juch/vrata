<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Http\Request;
use GuzzleHttp\Client;
use App\Routing\ApiRegistry;
use App\Services\DNSRegistry;
use App\Routing\RouteRegistry;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use App\Services\ServiceRegistryContract;
use Dusterio\LumenPassport\LumenPassport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Collection of providers supported by the API
        $this->app->singleton(ApiRegistry::class, function() {
            return new ApiRegistry(collect(Config::get('hub.apis')));
        });

        $this->app->singleton(RouteRegistry::class, function() {
            return new RouteRegistry($this->app->make(ApiRegistry::class));
        });

        $this->app->singleton(Request::class, function () {
            return $this->prepareRequest(Request::capture());
        });

        $this->app->bind(ServiceRegistryContract::class, DNSRegistry::class);

        $this->app->singleton(Client::class, function() {
            return new Client([
                'timeout' => Config::get('gateway.global.timeout'),
                'connect_timeout' => Config::get('gateway.global.connect_timeout', Config::get('gateway.global.timeout'))
            ]);
        });

        $this->app->alias(Request::class, 'request');

        $this->registerRoutes();

        Passport::tokensExpireIn(Carbon::now()->addDays(15));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
        LumenPassport::tokensExpireIn(Carbon::now()->addYears(50), 2);
        LumenPassport::allowMultipleTokens();
    }

    /**
     * Prepare the given request instance for use with the application.
     *
     * @param   Request $request
     * @return  Request
     */
    protected function prepareRequest(Request $request)
    {
        $request->setUserResolver(function () {
            return $this->app->make('auth')->user();
        })->setRouteResolver(function () {
            return $this->app->currentRoute;
        })->setTrustedProxies([
            '10.7.0.0/16', // Docker Cloud
            '103.21.244.0/22', // Cloud Flare
            '103.22.200.0/22',
            '103.31.4.0/22',
            '104.16.0.0/12',
            '108.162.192.0/18',
            '131.0.72.0/22',
            '141.101.64.0/18',
            '162.158.0.0/15',
            '172.64.0.0/13',
            '173.245.48.0/20',
            '188.114.96.0/20',
            '190.93.240.0/20',
            '197.234.240.0/22',
            '198.41.128.0/17',
            '199.27.128.0/21',
            '172.31.0.0/16', // Rancher
            '10.42.0.0/16' // Rancher
        ], \Illuminate\Http\Request::HEADER_X_FORWARDED_ALL);

        return $request;
    }

    /**
     * @return void
     */
    protected function registerRoutes()
    {
        $registry = $this->app->make(RouteRegistry::class);

        if ($registry->isEmpty()) {
            Log::info('Not adding any service routes - route file is missing');
            return;
        }

        $registry->bind(app());
    }
}
