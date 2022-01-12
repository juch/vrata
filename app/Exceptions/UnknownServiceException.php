<?php

namespace App\Exceptions;

use GuzzleHttp\Psr7\Response;

/**
 * Class UnableToExecuteRequestException
 * @package App\Exceptions
 */
class UnknownServiceException extends \Exception
{
    /**
     * UnknownServiceException constructor.
     * @param Response $response
     */
    public function __construct(Response $response = null, $service = 'null')
    {
        if ($response) {
            parent::__construct((string)$response->getBody(), $response->getStatusCode());
            return;
        }

        parent::__construct(sprintf('Unable to finish the request : service (%s) is unknown', $service), 500);
    }
}