<?php

namespace App\Controller;

use App\Entity\Parcelle;
use App\Form\ParcelleType;
use App\Repository\ParcelleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\WeatherService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/parcelle')]
final class ParcelleController extends AbstractController
{
    private $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    #[Route(name: 'app_parcelle_index', methods: ['GET'])]
    public function index(Request $request, ParcelleRepository $parcelleRepository): Response
    {
        $search = $request->query->get('search');
        $filterSuperficie = $request->query->get('filter_superficie');
        $filterCultureStatus = $request->query->get('filter_culture_status');

    $parcelles = $parcelleRepository->findBySearchAndFilters($search, $filterSuperficie, $filterCultureStatus);
        
        if ($search || $filterSuperficie || $filterCultureStatus) {
            $parcelles = $parcelleRepository->findBySearchAndFilters($search, $filterSuperficie, $filterCultureStatus);
        } else {
            $parcelles = $parcelleRepository->findAll();
        }

        // Choose template based on user role
        $template = $this->isGranted('ROLE_ADMIN') 
            ? 'parcelle/index_admin.html.twig'
            : 'parcelle/index.html.twig';

        return $this->render($template, [
            'parcelles' => $parcelles,
            'search' => $search,
            'filterSuperficie' => $filterSuperficie,
            'filterCultureStatus' => $filterCultureStatus,
        ]);
    }

    #[Route('/new', name: 'app_parcelle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $parcelle = new Parcelle();
        $form = $this->createForm(ParcelleType::class, $parcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($parcelle);
            $entityManager->flush();

            return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcelle/new.html.twig', [
            'parcelle' => $parcelle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_parcelle_show', methods: ['GET'])]
    public function show(Parcelle $parcelle,HttpClientInterface $httpClient): Response
    {
        $cultureParcelles = $parcelle->getCultureParcelles();

        // Get the localisation for the parcelle
        $parcelleLocation = $parcelle->getLocalisation();

        // Get weather based on the parcelle's location
        $weather = $this->weatherService->getWeatherByLocation($parcelleLocation);

        return $this->render('parcelle/show.html.twig', [
            'parcelle' => $parcelle,
            'cultureParcelles' => $cultureParcelles,
            'weather' => $weather,
            'parcelleLocation' => $parcelleLocation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_parcelle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Parcelle $parcelle, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParcelleType::class, $parcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcelle/edit.html.twig', [
            'parcelle' => $parcelle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_parcelle_delete', methods: ['POST'])]
    public function delete(Request $request, Parcelle $parcelle, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$parcelle->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($parcelle);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
    }
}
