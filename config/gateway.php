<?php

return (static function() {
    $configTemplate = [
        // List of microservices behind the gateway
        'services' => [
            'core' => [],
            'login' => [],
            'rds' => [],
            'tpt-salles' => [
                'hostname' => 'localhost:8081'
            ],
            'agro-salles' => [
                'hostname' => 'localhost:8082'
            ],
        ],

        // Array of extra (eg. aggregated) routes
        'routes' => [
            [
                'aggregate' => true,
                'method' => 'GET',
                // 'path' => '/v1/salles/{id}',
                'path' => '/salles',
                'public' => false,
                'raw' => false,
                'actions' => [
                    'tpt' => [
                        'service' => 'tpt-salles',
                        'method' => 'GET',
                        'path' => 'salles',
                        'sequence' => 0,
                        'output_key' => [
                            'data' => 'tpt',
                        ],
                        'critical' => false,
                    ],
                    'agro' => [
                        'service' => 'agro-salles',
                        'method' => 'GET',
                        'path' => 'salles',
                        'sequence' => 0,
                        'output_key' => [
                            'data' => 'agro',
                        ],
                        'critical' => false,
                    ],
                ],
            ],
        ],

        // Global parameters
        'global' => [
            'prefix' => '/v1',
            'timeout' => 2.0, // in seconds
            'connect_timeout' => 1.0, // in seconds
            'doc_point' => '/api/doc',
            'domain' => 'localhost:8888'
        ],

        // Header white list to forward to micro services
        'headers-forwarded-whitelist' => [
            'X-SYNAPSES-APP',
            'X-SYNAPSES-USER',
        ],
    ];

    $sections = ['services', 'routes', 'global', 'headers-forwarded-whitelist'];

    foreach ($sections as $section) {
        $config = env('GATEWAY_' . strtoupper($section), false);
        ${$section} = $config ? json_decode($config, true) : $configTemplate[$section];
        if (${$section} === null) throw new \Exception('Unable to decode GATEWAY_' . strtoupper($section) . ' variable');
    }

    return compact($sections);
})();
