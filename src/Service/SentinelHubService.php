<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class SentinelHubService
{
    private $client;
    private $clientId;
    private $clientSecret;
    private $logger;
    private $tokenUrl = 'https://services.sentinel-hub.com/oauth/token';
    private $apiUrl = 'https://services.sentinel-hub.com/api/v1/process';
    private $geocodeUrl = 'https://nominatim.openstreetmap.org/search';

    public function __construct(HttpClientInterface $client, string $clientId, string $clientSecret, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->logger = $logger;
    }

    public function getAccessToken(): string
    {
        $response = $this->client->request('POST', $this->tokenUrl, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]
        ]);

        $data = $response->toArray();
        if (!isset($data['access_token'])) {
            throw new \Exception('Failed to obtain Sentinel Hub access token.');
        }

        return $data['access_token'];
    }

    public function geocodeAddress(string $address): ?array
    {
        $response = $this->client->request('GET', $this->geocodeUrl, [
            'query' => ['q' => $address, 'format' => 'json', 'limit' => 1]
        ]);

        $data = $response->toArray();

        if (empty($data)) {
            return null;
        }

        return [
            'latitude' => (float) $data[0]['lat'],
            'longitude' => (float) $data[0]['lon'],
        ];
    }

    public function getSatelliteImage(string $location): ?string
    {
        $coordinates = $this->geocodeAddress($location);
        if (!$coordinates) {
            $this->logger->error("❌ Could not determine coordinates for: $location");
            throw new \Exception("Could not determine coordinates for: $location");
        }

        $latitude = $coordinates['latitude'];
        $longitude = $coordinates['longitude'];
        $bbox = [$longitude - 0.01, $latitude - 0.01, $longitude + 0.01, $latitude + 0.01];

        $accessToken = $this->getAccessToken();

        // Sentinel Hub API request
        $response = $this->client->request('POST', $this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                "input" => [
                    "bounds" => ["bbox" => $bbox],
                    "data" => [["type" => "sentinel-2-l2a"]]
                ],
                "output" => ["format" => "image/png"]
            ]
        ]);

        $imageData = $response->getContent();

        // Log response for debugging
        file_put_contents('sentinel_debug_response.txt', $imageData);
        $this->logger->info("✅ Satellite image successfully retrieved for: $location");

        return $imageData;
    }
}
