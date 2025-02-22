<?php
namespace App\Controller;

use App\Service\NASAService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NASAController extends AbstractController
{
    private $nasaService;

    public function __construct(NASAService $nasaService)
    {
        $this->nasaService = $nasaService;
    }

    #[Route('/nasa/satellite_data', name: 'app_nasa_data')]
    public function getSatelliteData(): Response
    {
        // Get agriculture-relevant data
        $data = $this->nasaService->getSatelliteData();
        
        return $this->render('nasa/satellite_data.html.twig', [
            'nasa_data' => $data,
        ]);
    }
}
?>
