<?php

// src/Service/CropRecommendationService.php

// src/Service/CropRecommendationService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CropRecommendationService
{
    private string $apiUrl;
    private HttpClientInterface $httpClient;

    public function __construct(string $apiUrl, HttpClientInterface $httpClient)
    {
        $this->apiUrl = $apiUrl;
        $this->httpClient = $httpClient;
    }

    public function getRecommendedCrop(float $temperature, float $humidity, float $ph, float $rainfall): ?string
    {
        $payload = [
            'temperature' => $temperature,
            'humidity' => $humidity,
            'ph' => $ph,
            'rainfall' => $rainfall,
        ];

        try {
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'json' => $payload,
            ]);

            $data = $response->toArray();

            return $data['recommended_crop'] ?? null;

        } catch (\Exception $e) {
            return null;
        }
    }
}

?>

