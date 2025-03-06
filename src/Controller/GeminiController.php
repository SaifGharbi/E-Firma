<?php

namespace App\Controller;

use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GeminiController extends AbstractController
{
    #[Route('/gemini', name: 'app_gemini', methods: ['GET', 'POST'])]
    public function index(Request $request, GeminiService $geminiService): Response
    {
        $responseText = null; // Initialize responseText

        if ($request->isMethod('POST')) {
            $userMessage = $request->request->get('message');
            if ($userMessage) {
                $responseText = $geminiService->getGeminiResponse($userMessage);
            }
        }

        return $this->render('gemini/index.html.twig', [
            'responseText' => $responseText, // Ensure it's passed to Twig
        ]);
    }
}
