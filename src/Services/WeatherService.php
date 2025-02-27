<?php

// src/Service/WeatherService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private string $apiKey;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function getWeatherByLocation(string $location): array
    {
        $url = sprintf(
            'https://api.openweathermap.org/data/2.5/weather?q=%s&appid=%s&units=metric&lang=fr',
            urlencode($location),
            $this->apiKey
        );

        $response = $this->httpClient->request('GET', $url);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to fetch weather data.');
        }

        return $response->toArray();
    }
}



?>