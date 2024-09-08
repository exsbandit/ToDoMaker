<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProviderFactory
{
    private $client;
    private $param;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $param)
    {
        $this->client = $client;
        $this->param = $param;

    }

    public function getProvider(string $provider): ProviderInterface
    {
        switch ($provider) {
            case 'provider1':
                return new Provider1($this->client, $this->param->get('provider1_api_url'));
            case 'provider2':
                return new Provider2($this->client, $this->param->get('provider2_api_url'));
            default:
                throw new \InvalidArgumentException('Unknown provider: ' . $provider);
        }
    }
}