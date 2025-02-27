<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends AbstractController
{
    private WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    #[Route('/api/weather', name: 'app_weather_get', methods: ['GET'])]
    public function getWeather(Request $request): JsonResponse
    {
        $lat = $request->query->get('lat');
        $lon = $request->query->get('lon');

        if (!$lat || !$lon) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Latitude and longitude are required'
            ], 400);
        }

        try {
            $weather = $this->weatherService->getWeatherByLocation((float)$lat, (float)$lon);
            return new JsonResponse($weather);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Weather service error: ' . $e->getMessage()
            ], 500);
        }
    }
} 