<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private const API_KEY = "AIzaSyBsbmDCz1KXXnsfUYP0GzupdiatohsIq04"; // Replace with your actual API key
    private const API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=";

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getGeminiResponse(string $userMessage): string
    {
        $requestUrl = self::API_URL . self::API_KEY;

        $jsonRequest = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $userMessage]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->httpClient->request('POST', $requestUrl, [
                'json' => $jsonRequest,
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return "❌ API Error: " . $statusCode;
            }

            $data = $response->toArray();
            return $this->extractResponse($data);

        } catch (\Exception $e) {
            return "❌ Exception: " . $e->getMessage();
        }
    }

    private function extractResponse(array $jsonResponse): string
    {
        return $jsonResponse['candidates'][0]['content']['parts'][0]['text'] ?? "❌ Error parsing response!";
    }
}
