<?php

return (static function() {
    $configTemplate = [
        // List of api used by hub
        'apis' => [
            'tpt' => [
                'hostname' => 'localhost:8081',
                'public' => true,
                'role' => 'TPT',
                'api-key' => '',
            ],
            'agro' => [
                'hostname' => 'localhost:8082',
                'public' => true,
                'role' => 'AGRO',
                'api-key' => '',
            ],
            'ensta' => [
                'hostname' => 'localhost:8083',
                'public' => true,
                'role' => 'ENSTA',
                'api-key' => '',
            ],
            'iogs' => [
                'hostname' => 'localhost:8084',
                'public' => true,
                'role' => 'IOGS',
                'api-key' => '',
            ],
            'x' => [
                'hostname' => 'localhost:8085',
                'public' => true,
                'role' => 'X',
                'api-key' => '',
            ],
        ],
    ];

    $sections = ['apis'];

    foreach ($sections as $section) {
        $config = env('HUB_' . strtoupper($section), false);
        ${$section} = $config ? json_decode($config, true) : $configTemplate[$section];
        if (${$section} === null) throw new \Exception('Unable to decode HUB_' . strtoupper($section) . ' variable');
    }

    return compact($sections);
})();
