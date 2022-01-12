<?php

namespace App\Services;


class ApiProvider 
{
    private $name;
    private $hostname;
    private $public;
    private $role;
    private $apiKey;

    public function __construct(string $name, array $config) 
    {
        $this->name = $name;
        $this->hostname = $config['hostname'];
        $this->public = $config['public'] ?? false;
        $this->role = $config['role'] ?? null;
        $this->apiKey = $config['api-key'] ?? null;
    }

    public function getName(): string 
    {
        return $this->name;
    }

    public function getHostname(): string 
    {
        return $this->hostname;
    }

    public function isPublic(): boolean 
    {
        return $this->public;
    }

    public function getRole(): string 
    {
        return $this->role;
    }

    public function getApiKey(): string 
    {
        return $this->apiKey;
    }

    /**
     * Returns provider's name and url
     *
     * @return array
     */
    public function getProviderInfo() 
    {
        return [$this->name => $this->hostname];
    }
}