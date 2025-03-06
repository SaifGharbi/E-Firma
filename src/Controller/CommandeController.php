<?php

namespace App\Controller;

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\LivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[Route('/commande')]
final class CommandeController extends AbstractController
{
    #[Route(name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/back', name: 'app_commande_index_back', methods: ['GET'])]
    public function index_back(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index_back.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }
///redirectiion badeletha hne
    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager, Security $security): Response
    {
        if (!$commande->getStatut()) {  // ✅ Ensure statut is always set
            $commande->setStatut('Not Treated');
        }

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('You must be logged in to edit an order.');
            }

            if ($commande->getUser() !== $user) {
                $commande->setUser($user);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_my_orders', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
            'button_label' => 'Update Order',
        ]);
    }
///badelt hne redirection

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_my_orders', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/my-orders/aaaa', name: 'app_my_orders', methods: ['GET'])]
    public function viewMyOrders(CommandeRepository $commandeRepository, LivraisonRepository $livraisonRepository, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view your orders.');
        }

        // Fetch all commandes for the user
        $commandes = $commandeRepository->findBy(['user' => $user], ['date' => 'DESC']);

        // For each commande, fetch its livraison (if any) using commande_id
        foreach ($commandes as $commande) {
            $livraison = $livraisonRepository->findOneBy(['commande' => $commande]);
            $commande->livraison = $livraison; // Temporarily attach livraison to commande for template rendering
        }

        return $this->render('commande/my_orders.html.twig', [
            'commandes' => $commandes,
        ]);
    }
//hedhi zedtha
    #[Route('/admin/orders', name: 'app_admin_orders', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function viewAllOrders(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();

        return $this->render('commande/admin_orders.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/{id}/mark-treated', name: 'app_commande_mark_treated', methods: ['POST'])]
    public function markAsTreated(Commande $commande, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('mark_treated' . $commande->getId(), $request->request->get('_token'))) {
            $commande->setStatut('Treated'); // Or 'Traité' for French consistency
            $entityManager->flush();

            $this->addFlash('success', 'Order marked as treated successfully.');
        }

        return $this->redirectToRoute('app_commande_index_back', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/stats_commande/stat', name: 'app_stats_top', methods: ['GET'])]
    public function showTopStats(CommandeRepository $commandeRepository): Response
    {
        $topCustomers = $commandeRepository->getTopCustomersByOrderValue(5); // Top 5 customers
        $popularLocations = $commandeRepository->getMostPopularDeliveryLocations(5); // Top 5 locations

        return $this->render('commande/top_stats.html.twig', [
            'topCustomers' => $topCustomers,
            'popularLocations' => $popularLocations,
        ]);
    }
}