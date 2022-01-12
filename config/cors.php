<?php

return [
    'supportsCredentials' => false,
    'allowedOrigins' => ['*'],
    'allowedHeaders' => ['Content-Type', 'Accept', 'Authorization', 'Origin', 'X-SYNAPSES-APP', 'X-SYNAPSES-USER', 'X-SYNAPSES-USER-UUID', 'X-SYNAPSES-PROVIDER'],
    'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'exposedHeaders' => [],
    'maxAge' => 0,
    'hosts' => []
];