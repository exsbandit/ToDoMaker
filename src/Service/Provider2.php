<?php

namespace App\Service;

class Provider2 implements ProviderInterface
{
    private $client;

    public function __construct($client, string $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    public function fetchTasks(): array
    {
        $response = $this->client->request('GET',  $this->url);
        $data = $response->toArray();
        $tasks = [];
        foreach ($data as $item) {
            $tasks[] = [
                'id' => $item['id'],
                'difficulty' => $item['zorluk'], // Zorluk
                'duration' => $item['sure'] // Süre
            ];
        }
        return $tasks;
    }
}