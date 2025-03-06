<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\RendezVousRepository;
use App\Repository\ServiceRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rendez/vous')]
final class RendezVousController extends AbstractController
{
    #[Route(name: 'app_rendez_vous_index', methods: ['GET'])]
    public function index(RendezVousRepository $rendezVousRepository, Request $request): Response
    {
        // Get search input from query parameters
        $searchInput = $request->query->get('searchInput', '');

        // Fetch all RendezVous (or filtered by search)
        $queryBuilder = $rendezVousRepository->createQueryBuilder('r')
            ->orderBy('r.date', 'DESC');

        if ($searchInput) {
            $queryBuilder->where('r.id LIKE :search OR r.date LIKE :search OR r.statut LIKE :search')
                ->setParameter('search', '%' . $searchInput . '%');
        }

        $query = $queryBuilder->getQuery();
        $allRendezVous = $query->getResult();

        // Manual pagination
        $perPage = 3; // Items per page
        $currentPage = max(1, $request->query->getInt('page', 1)); // Current page, default to 1
        $totalItems = count($allRendezVous);
        $totalPages = ceil($totalItems / $perPage);

        // Slice the array for the current page
        $offset = ($currentPage - 1) * $perPage;
        $rendezVouses = array_slice($allRendezVous, $offset, $perPage);

        return $this->render('rendez_vous/index.html.twig', [
            'rendez_vouses' => $rendezVouses,
            'searchInput' => $searchInput,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'perPage' => $perPage,
        ]);
    }

    #[Route('/vet-expert', name: 'app_commande_index_vet_exp', methods: ['GET'])]
    public function index_vet_exp(RendezVousRepository $rendezVousRepository, Request $request): Response
    {
        // Fetch all appointments (you can filter or modify if necessary)
        $allRendezVous = $rendezVousRepository->findAll();
    
        // Manual pagination logic
        $perPage = 3; // Number of items per page
        $currentPage = max(1, $request->query->getInt('page', 1)); // Current page, default to 1
        $totalItems = count($allRendezVous);
        $totalPages = ceil($totalItems / $perPage);
    
        // Slice the array for the current page
        $offset = ($currentPage - 1) * $perPage;
        $rendezVouses = array_slice($allRendezVous, $offset, $perPage);
    
        return $this->render('rendez_vous/index_vet_expert.html.twig', [
            'rendez_vouses' => $rendezVouses,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'perPage' => $perPage,
        ]);
    }
    
    #[Route('/update-status/{id}', name: 'app_rendez_vous_update_status', methods: ['POST'])]
public function updateStatus(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
{
    $data = json_decode($request->getContent(), true);
    
    if (!isset($data['status'])) {
        return $this->json(['error' => 'Invalid request'], 400);
    }

    // Ensure `setStatut()` exists in `RendezVous.php`
    $rendezVou->setStatut($data['status']); 

    $entityManager->flush();

    return $this->json([
        'status' => $rendezVou->isStatut() ? 'Confirmed' : 'Pending',
        'statusClass' => $rendezVou->isStatut() ? 'bg-success' : 'bg-warning',
    ]);
}
    
    
    #[Route('/new', name: 'app_rendez_vous_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ServiceRepository $serviceRepository, MailService $mailService): Response
    {
        $rendezVou = new RendezVous();

        // Set statut to false (0) by default for new appointments
        $rendezVou->setStatut(false);

        // Set user from session (current authenticated user)
        $user = $this->getUser();
        if ($user) {
            $rendezVou->setUser($user); // Pre-populate the user field
        } else {
            throw $this->createAccessDeniedException('You must be logged in to schedule an appointment.');
        }

        // Check for serviceId in query parameters
        $serviceId = $request->query->get('serviceId');
        $service = null;
        if ($serviceId) {
            $service = $serviceRepository->find($serviceId);
            if ($service) {
                $rendezVou->setService($service); // Pre-populate the service field
            } else {
                throw $this->createNotFoundException('Service not found for ID: ' . $serviceId);
            }
        } else {
            throw new \Exception('A service ID is required to schedule an appointment.');
        }

        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rendezVou);
            $entityManager->flush();

            try {
                // Use the user's email instead of a hardcoded address
                $mailService->sendRendezVousConfirmationEmail(
                    'nourane.lammouchi@esprit.tn',
                    $rendezVou->getDate(),
                    $rendezVou->getService()->getNom(),
                    $rendezVou->isStatut()
                );
                $this->addFlash('success', 'Appointment scheduled successfully and confirmation email sent.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Appointment scheduled, but failed to send confirmation email: ' . $e->getMessage());
            }

            return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rendez_vous/new.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
            'serviceId' => $serviceId, // Pass the serviceId to the template
            'service' => $service,     // Optionally pass the Service object
        ]);
    }

    #[Route('/{id}', name: 'app_rendez_vous_show', methods: ['GET'])]
    public function show(RendezVous $rendezVou): Response
    {
        return $this->render('rendez_vous/show.html.twig', [
            'rendez_vou' => $rendezVou,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rendez_vous_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        // Set statut to false (0) by default for editing (if not already set)
        if ($rendezVou->isStatut() !== false) {
            $rendezVou->setStatut(false);
        }

        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rendez_vous/edit.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rendez_vous_delete', methods: ['POST'])]
    public function delete(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rendezVou->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rendezVou);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/my-appointments/aaa', name: 'app_rendez_vous_my', methods: ['GET'])]
    public function myRendezvous(RendezVousRepository $rendezVousRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view your appointments.');
        }

        // Fetch all rendezvous associated with the current user
        $rendezVous = $rendezVousRepository->findBy(['user' => $user]);

        return $this->render('rendez_vous/my_rendezvous.html.twig', [
            'rendez_vouses' => $rendezVous,
        ]);
    }

}