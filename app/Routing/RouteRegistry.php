<?php

namespace App\Routing;

use Webpatser\Uuid\Uuid;
use App\Routing\ApiRegistry;
use Laravel\Lumen\Application;
use Illuminate\Support\Facades\Storage;

/**
 * Class RouteRegistry
 * @package App\Routing
 */
class RouteRegistry
{
    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @var ApiRegistry
     */
    protected $apiRegistry = null;

    /**
     * RouteRegistry constructor.
     */
    public function __construct(ApiRegistry $apiRegistry, string $filename = null)
    {
        $this->apiRegistry = $apiRegistry;

        $filename = $filename ?: 'routes.json';

        if (Storage::exists($filename)) {
            $routes = json_decode(Storage::get($filename), true);
            if ($routes !== null) {
                // We want to re-parse config routes to allow route overwriting
                $this->parseRoutes($routes);
            }
        }

        $this->parseConfigRoutes();
    }

    /**
     * @param RouteContract $route
     */
    public function addRoute(RouteContract $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->routes);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getRoutes()
    {
        return collect($this->routes);
    }

    /**
     * @param string $id
     * @return RouteContract
     */
    public function getRoute($id)
    {
        return collect($this->routes)->first(function ($route) use ($id) {
            return $route->getId() == $id;
        });
    }

    /**
     * @param Application $app
     */
    public function bind(Application $app)
    {
        $this->getRoutes()->each(function ($route) use ($app) {
            $method = strtolower($route->getMethod());

            $middleware = [ 'helper:' . $route->getId() ];
            if (! $route->isPublic()) {
                $middleware[] = 'auth';
                $middleware[] = 'perimeters';
            }
            $app->router->{$method}($route->getPath(), [
                'uses' => 'App\Http\Controllers\GatewayController@' . $method,
                'middleware' => $middleware
            ]);
        });
    }

    /**
     * @return $this
     */
    private function parseConfigRoutes()
    {
        $config = config('gateway');
        if (!empty($config)) {
            $this->parseRoutes($config['routes']);
        }

        return $this;
    }

    /**
     * @param array $routes
     * @return $this
     */
    private function parseRoutes(array $routes)
    {
        collect($routes)->each(function ($routeDetails) {
            if (! isset($routeDetails['id'])) {
                $routeDetails['id'] = (string)Uuid::generate(4);
            }

            $route = new Route($routeDetails);
            $this->apiRegistry->handleRoute($route, $routeDetails);
            $this->addRoute($route);
        });

        return $this;
    }
}
