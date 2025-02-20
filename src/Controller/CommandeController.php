<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commande')]
final class CommandeController extends AbstractController
{
    #[Route(name: 'app_commande_index', methods: ['GET'])]
    public function index(SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        $commande = $session->get('commande', []);
        $commandeItems = [];
        $total = 0; // Initialize total price

        foreach ($commande as $produitId => $quantite) {
            $produit = $produitRepository->find($produitId);
            if ($produit) {
                $commandeItems[] = [
                    'produit' => $produit,
                    'quantity' => $quantite
                ];
                $total += $produit->getPrix() * $quantite; // Calculate total price
            }
        }

        return $this->render('commande/index.html.twig', [
            'commandeItems' => $commandeItems,
            'commande' => $commande,
            'total' => $total // Pass total to template
        ]);
    }

    #[Route('/commande/add', name: 'commande_add', methods: ['POST'])]
    public function addToCart(Request $request, ProduitRepository $produitRepository, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $productId = $data['id'] ?? null;

        if (!$productId) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid product ID']);
        }

        $commande = $session->get('commande', []);
        if (isset($commande[$productId])) {
            $commande[$productId]++;
        } else {
            $commande[$productId] = 1;
        }

        $session->set('commande', $commande);

        return new JsonResponse([
            'success' => true,
            'commandeCount' => array_sum($commande)
        ]);
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        $commande = new Commande();
        $commande->setDate(new \DateTime()); // Set today's date
        $commande->setStatut(0); // Set status as "not treated"

        // Retrieve cart from session
        $cart = $session->get('commande', []);
        $commandeItems = [];
        $total = 0;

        foreach ($cart as $produitId => $quantite) {
            $produit = $produitRepository->find($produitId);
            if ($produit) {
                $commandeItems[] = [
                    'produit' => $produit,
                    'quantity' => $quantite
                ];
                $total += $produit->getPrix() * $quantite;
            }
        }

        // Set the total price in the form
        $commande->setTotal($total);

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();

            // Clear the cart after checkout
            $session->remove('commande');

            return $this->redirectToRoute('app_livraison_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/new.html.twig', [
            'commande' => $commande,
            'commandeItems' => $commandeItems, // Pass cart items to the template
            'total' => $total,
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

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}
