<?php

namespace App\Services;

use App\Exceptions\UnknownServiceException;

/**
 * Class DNSRegistry
 * @package App\Services
 */
class DNSRegistry implements ServiceRegistryContract
{
    /**
     * @param string $serviceId
     * @return string
     */
    public function resolveInstance($serviceId)
    {
        $gatewayConfig = config('gateway');
        $hubConfig = config('hub');

        if (empty($hubConfig)) {
            // If service doesn't have a specific URL, simply append global domain to service name
            $hostname = $gatewayConfig['services'][$serviceId]['hostname'] ?? $serviceId . '.' . $gatewayConfig['global']['domain'];
        } else {
            // If hubs are defined service name concatain hubname%servicename
            if (false === strpos($serviceId, '%')) {
                throw new UnknownServiceException(null, $serviceId);
            }
            list($apiId, $serviceId) = explode('%', $serviceId);
            $hostname = $hubConfig['apis'][$apiId]['hostname'] ?? $gatewayConfig['global']['domain'];
            $hostname = isset($gatewayConfig['services'][$serviceId]['path']) ? 
                $hostname . '/' . $gatewayConfig['services'][$serviceId]['path'] :
                $serviceId . '.' . $hostname;
        }

        return "http://$hostname";
    }
}