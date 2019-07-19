<?php

namespace App\Http\Middleware;

use App\Http\Request;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest() && ! app()->environment('local')) {
            return response(json_encode([
                'status' => 401,
                'title' => 'Unauthorized',
                'detail' => 'This ressource require Authentication. Ask for token first at /oauth/token',
                'type' => 'https://example.net/authentication-error',
                'instance' => null,
                'invalid-params' => null
            ]), 401)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Content-Type',  'application/problem+json');
        }

        return $next($request);
    }
}
