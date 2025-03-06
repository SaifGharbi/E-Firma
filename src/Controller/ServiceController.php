<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\RendezVousRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security; // Add this import

#[Route('/service')]
final class ServiceController extends AbstractController
{
    #[Route(name: 'app_service_index', methods: ['GET'])]
    public function index(ServiceRepository $serviceRepository): Response
    {
        return $this->render('service/index.html.twig', [
            'services' => $serviceRepository->findAll(),
        ]);
    }


    //ajouter servie fl front
    #[Route('/manage',name: 'app_service_index_manage', methods: ['GET'])]
    public function manage(ServiceRepository $serviceRepository): Response
    {
        return $this->render('service/service_mangement.html.twig', [
            'services' => $serviceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_service_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user from the security context (authenticated user)
            $user = $security->getUser();
            if ($user) {
                $service->setUser($user); // Associate the service with the logged-in user
            } else {
                throw $this->createAccessDeniedException('You must be logged in to create a service.');
            }

            $entityManager->persist($service);
            $entityManager->flush();

            return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('service/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        return $this->render('service/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager, Security $security): Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure the user is still associated with the service (optional, depending on your logic)
            $user = $security->getUser();
            if ($user) {
                $service->setUser($user); // Re-associate with the current user
            } else {
                throw $this->createAccessDeniedException('You must be logged in to edit a service.');
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('service/edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/like', name: 'service_like', methods: ['POST'])]
    public function likeService(int $id, SessionInterface $session): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $likes = $session->get("service_{$id}_likes", 0);
        $dislikes = $session->get("service_{$id}_dislikes", 0);

        $likes++; // Increment like count
        $session->set("service_{$id}_likes", $likes);

        return $this->json([
            'likes' => $likes,
            'dislikes' => $dislikes,
        ]);
    }

    #[Route('/{id}/dislike', name: 'service_dislike', methods: ['POST'])]
    public function dislikeService(int $id, SessionInterface $session): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $likes = $session->get("service_{$id}_likes", 0);
        $dislikes = $session->get("service_{$id}_dislikes", 0);

        $dislikes++; // Increment dislike count
        $session->set("service_{$id}_dislikes", $dislikes);

        return $this->json([
            'likes' => $likes,
            'dislikes' => $dislikes,
        ]);
    }
    #[Route('/stats/aaaa', name: 'app_service_rendez_vous_stats', methods: ['GET'])]
    public function showServiceAndRendezVousStats(ServiceRepository $serviceRepository, RendezVousRepository $rendezVousRepository): Response
    {
        // Fetch services data
        $services = $serviceRepository->findAll();
        $serviceStats = $serviceRepository->getServiceStatsByType(); // Assuming this method exists

        // Fetch appointments data
        $appointments = $rendezVousRepository->findAll();
        $appointmentStats = $rendezVousRepository->getAppointmentsByService(); // Assuming this method exists

        return $this->render('service/service_rendez_vous_stats.html.twig', [
            'services' => $services,
            'serviceStats' => $serviceStats,
            'appointments' => $appointments,
            'appointmentStats' => $appointmentStats,
        ]);
    }
}