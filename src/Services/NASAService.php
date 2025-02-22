<?php 
namespace App\Service;

use GuzzleHttp\Client;

class NASAService
{
    private $client;
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->client = new Client();
        $this->apiKey = $apiKey;
    }

    public function getSatelliteData(): array
{
    $latitude = 34.0;
    $longitude = 9.5;

    try {
        $response = $this->client->request('GET', 'https://api.nasa.gov/planetary/earth/assets', [
            'query' => [
                'lat' => $latitude,
                'lon' => $longitude,
                'dim' => 0.5,
                'api_key' => $this->apiKey,
                'cloud_score' => true,
                'date' => '2025-02-1', // Use today's date dynamically
            ]
        ]);

        $responseBody = $response->getBody()->getContents();
        file_put_contents('nasa_debug.log', $responseBody); // Log response for debugging

        $data = json_decode($responseBody, true);

        if (isset($data['error'])) {
            return ['error' => 'NASA API Error: ' . $data['error']['message']];
        }

        if (!isset($data['url'])) {
            return ['error' => 'No image URL in response'];
        }

        return [
            'date' => isset($data['date']) ?? 'Unknown',
            'image_url' => $data['url'],
            'cloud_coverage' => isset($data['cloud_score']) ? $data['cloud_score'] : 'N/A',
            'dataset' => $data['resource']['dataset'] ?? 'N/A',
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]
        ];
    } catch (\Exception $e) {
        return ['error' => 'NASA API error: ' . $e->getMessage()];
    }
}


}
?>

