<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddUserPerimeters
{
    /**
     * Create a new middleware instance.
     *
     * @param  App\UserPerimeters  $model
     * 
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $request->providers = null;
        if ($request->header('X-SYNAPSES-PROVIDER')) {
            // voir à être géré par un autre middleware ?
            // voir pour mettre à jour la classe Request pour prendre en compte
            $request->providers = explode(',', strtoupper($request->header('X-SYNAPSES-PROVIDER'))); 
        }
        try {
            $userPerimeters = \App\UserPerimeters::findOrFail($request->header('X-SYNAPSES-USER-UUID'));
            $request->userPerimeters = $userPerimeters;
        } catch (QueryException $e) {
            return response(json_encode([
                'status' => 400,
                'title' => 'Bad Parameters',
                'detail' => 'This request contains bad parameters',
                'type' => 'https://example.net/bad-parameters-error',
                'instance' => null,
                'invalid-params' => null
            ]), 400)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Content-Type',  'application/problem+json');
        } catch (ModelNotFoundException $e) {
            return response(json_encode([
                'status' => 403,
                'title' => 'Forbidden',
                'detail' => 'This ressource require authorization. You need to Authenticate first',
                'type' => 'https://example.net/authorization-error',
                'instance' => null,
                'invalid-params' => null
            ]), 403)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Content-Type',  'application/problem+json');
        }
        
        return $next($request);
    }
}
