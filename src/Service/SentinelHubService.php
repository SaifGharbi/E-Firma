<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class SentinelHubService
{
    private HttpClientInterface $client;
    private string $clientId;
    private string $clientSecret;
    private LoggerInterface $logger;
    private $cache;

    private string $tokenUrl = 'https://services.sentinel-hub.com/oauth/token';
    private string $apiUrl = 'https://services.sentinel-hub.com/api/v1/process';
    private string $geocodeUrl = 'https://nominatim.openstreetmap.org/search';

    public function __construct(HttpClientInterface $client, string $clientId, string $clientSecret, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->logger = $logger;
        $this->cache = new FilesystemAdapter(); // Caches token to reduce API calls
    }

    

    public function getAccessToken(): string
    {
        $cachedToken = $this->cache->getItem('sentinel_access_token');

        if (!$cachedToken->isHit()) {
            try {
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

                // Cache the token for 55 minutes (Sentinel tokens typically last 1 hour)
                $cachedToken->set($data['access_token']);
                $cachedToken->expiresAfter(3300);
                $this->cache->save($cachedToken);
            } catch (\Exception $e) {
                $this->logger->error('Sentinel Hub token request failed: ' . $e->getMessage());
                throw $e;
            }
        }

        return $cachedToken->get();
    }

    public function getSatelliteImage(string $location, float $bufferSize = 0.02): ?string
    {
        $coordinates = $this->geocodeAddress($location);
        if (!$coordinates) {
            throw new \Exception("Could not determine coordinates for location: $location");
        }

        $latitude = $coordinates['latitude'];
        $longitude = $coordinates['longitude'];

        $accessToken = $this->getAccessToken();

        try {
            $response = $this->client->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
    "input" => [
        "bounds" => ["bbox" => [$longitude - 0.01, $latitude - 0.01, $longitude + 0.01, $latitude + 0.01]],
        "data" => [["type" => "sentinel-2-l2a"]]
    ],
    "output" => ["format" => "image/png"],
    'evalscript' => "
    function setup() {
        return {
            input: ['B04', 'B03', 'B02'],
            output: { bands: 3 }
        };
    }

    function evaluatePixel(sample) {
        // Apply a simple contrast boost
        return [sample.B04 * 2, sample.B03 * 2, sample.B02 * 2];
        // return [sample.B04, sample.B03, sample.B02];
    }
"
]

            ]);
        
            return $response->getContent();
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            $this->logger->error("Sentinel Hub API error: " . $e->getMessage());
        
            // Log detailed API response
            $errorResponse = $e->getResponse()->getContent(false);
            $this->logger->error("Sentinel Hub Response: " . $errorResponse);
        
            return null;
        }
        
    }

    public function geocodeAddress(string $address): ?array
    {
        try {
            $response = $this->client->request('GET', $this->geocodeUrl, [
                'query' => [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                ]
            ]);

            $data = $response->toArray();
            if (empty($data)) {
                return null;
            }

            return [
                'latitude' => (float) $data[0]['lat'],
                'longitude' => (float) $data[0]['lon'],
            ];
        } catch (\Exception $e) {
            $this->logger->error("Geocoding request failed: " . $e->getMessage());
            return null;
        }
    }
}
