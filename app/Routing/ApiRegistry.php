<?php

namespace App\Routing;

use App\Routing\Route;
use App\Services\ApiProvider;
use Illuminate\Support\Collection;

class ApiRegistry 
{
    /**
     * list of APIS provider
     *
     * @var Collection
     */
    private $providers;

    /**
     * Undocumented function
     *
     * @param Collection $providers
     */
    public function __construct(Collection $providersConfig)
    {
        $providers = collect();
        $providersConfig->each(function($config, $name) use ($providers) {
            $providers->put($name, new ApiProvider($name, $config));
        });
        $this->providers = $providers;
    }

    /**
     * Fetches images utilizing different providers
     *
     * @param array $params query parameters
     * @return void
     */
    public function handleRoute(Route $route, $routeDetails)
    {
        if ($this->isHub()) {
            // actions can be empty ????
            if (empty($routeDetails['actions'])) {
                $this->providers->each(function($provider, $name) use ($route, $routeDetails) {
                    $route->addAction(new Action([
                        'alias' => $name,
                        'api' => $name, // is needed ??
                        'service' => '',
                        'method' => $routeDetails['method'],
                        'path' => $routeDetails['path'],
                        'sequence' => 0,
                        'output_key' => [
                            'data' => $name,
                        ],
                        'critical' => true,
                    ]));
                });
            } else {
                collect($routeDetails['actions'])->each(function ($action, $alias) use ($route) {
                    $this->providers->each(function($provider) use ($action, $alias, $route) {
                        if ($action['method'] === 'GET') {
                            $route->addAction(new Action(array_merge($action, [
                                // 'alias' => "{$provider->getName()}-$alias",
                                'alias' => $provider->getName(),
                                // 'alias' => $alias,
                                'api' => $provider->getName(),
                                'service' => "{$provider->getName()}%{$action['service']}",
                                'role' => $provider->getRole(),
                                'input_key' => "{$provider->getName()}.{$action['input_key']}",
                                // 'output_key' => [
                                //     'data' => $provider->getName(),
                                // ],
                            ])));
                        } else {
                            $route->addAction(new Action(array_merge($action, [
                                // 'alias' => "{$provider->getName()}-$alias",
                                'alias' => $provider->getName(),
                                // 'alias' => $alias,
                                'api' => $provider->getName(),
                                'service' => "{$provider->getName()}%{$action['service']}",
                                'role' => $provider->getRole(),
                                // 'output_key' => [
                                //     'data' => $provider->getName(),
                                // ],
                            ])));
                        }
                    });
                });
            }
        } else {
            collect($routeDetails['actions'])->each(function ($action, $alias) use ($route) {
                $route->addAction(new Action(array_merge($action, ['alias' => $alias])));
            });
        }
    }

    /**
     * Is Application is a hub or not
     *
     * @return boolean
     */
    public function isHub()
    {
        return !$this->providers->isEmpty();
    }

    /**
     * Returns a list of currently supported providers
     *
     * @return Collection
     */
    public function getHubs() 
    {
        return $this->providers->map(function($provider) {
            return $provider;
        });
    }
}

