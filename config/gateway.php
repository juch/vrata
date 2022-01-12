<?php

return (static function() {
    $configTemplate = [
        // List of microservices behind the gateway
        'services' => [
            'core' => [
                'hostname' => '',
                'path' => '',
            ],
        ],

        // Array of extra (eg. aggregated) routes
        'routes' => [
            [
                'method' => 'POST',
                'path' => '/user/login',
                'public' => true,
                'raw' => false,
                'merge_actions_output' => false,
                'actions' => [
                    'user' => [
                        'service' => 'core',
                        'method' => 'GET',
                        'path' => 'user/{origin%email}',
                        'sequence' => 0,
                        'critical' => false,
                        'input_key' => 'data',
                        'output_key' => [
                            'items' => 'items'
                        ],
                    ],
                ],
            ],
            [
                'method' => 'GET',
                'path' => '/salles',
                'public' => false,
                'raw' => false,
                'merge_actions_output' => true,
                'actions' => [
                    'salle' => [
                        'service' => 'core',
                        'method' => 'GET',
                        'path' => 'salles',
                        'sequence' => 0,
                        'critical' => false,
                        'input_key' => 'data',
                        'output_key' => [
                            'items' => 'items'
                        ],
                    ],
                ],
            ],
            [
                'method' => 'POST',
                'path' => '/reservations',
                'public' => false,
                'raw' => false,
                'merge_actions_output' => true,
                'actions' => [
                    'reservations' => [
                        'service' => 'core',
                        'method' => 'POST',
                        'path' => 'reservations',
                        'sequence' => 0,
                        'critical' => false, // sera critical quand le bon provider sera sélectionné automatiquement
                        'body' => [ // paramètre pour pouvoir reporter automatiquement tout le body ???
                            "datedebut" => "{origin%datedebut}",
                            "datefin" => "{origin%datefin}",
                            "intitule" => "{origin%intitule}",
                            "idbeneficiaire" => "{origin%idbeneficiaire}",
                            "uuidbeneficiaire" => "{origin%uuidbeneficiaire}",
                            "motif" => "{origin%motif}",
                            "forcerValidation" => "{origin%forcerValidation}",
                            "checkAvailability" => "{origin%checkAvailability}",
                            "idperscreat" => "{origin%idperscreat}",
                            "idlocal" => "{origin%idlocal}",
                            "uuidlocal" => "{origin%uuidlocal}"
                        ],
                        'input_key' => 'data'
                    ],
                ],
            ],
        ],

        // Global parameters
        'global' => [
            'prefix' => '/v1',
            'timeout' => 120.0, // in seconds
            'service_timeout' => 5.0, // in seconds
            'connect_timeout' => 2.0, // in seconds
            'doc_point' => '/api/doc',
            'domain' => 'localhost:8888'
        ],

        // Header white list to forward to micro services
        'headers-forwarded-whitelist' => [
            'X-SYNAPSES-APP',
            'X-SYNAPSES-USER',
            'X-SYNAPSES-USER-UUID',
            'X-SYNAPSES-USER-PROVIDER',
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
