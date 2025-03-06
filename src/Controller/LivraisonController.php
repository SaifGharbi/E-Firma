<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Livraison;
use App\Form\LivraisonType;
use App\Repository\LivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/livraison')]
final class LivraisonController extends AbstractController
{
    #[Route(name: 'app_livraison_index', methods: ['GET'])]
    public function index(LivraisonRepository $livraisonRepository): Response
    {
        return $this->render('livraison/index.html.twig', [
            'livraisons' => $livraisonRepository->findAll(),
        ]);
    }

    #[Route('/cart/add-livraison', name: 'cart_add_livraison', methods: ['GET', 'POST'])]
    public function addLivraison(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $commandeId = $session->get('commande_id', null);

        if (!$commandeId) {
            $this->addFlash('danger', 'No order found in session.');
            return $this->redirectToRoute('app_produit_index');
        }

        $commande = $entityManager->getRepository(Commande::class)->find($commandeId);

        if (!$commande) {
            $this->addFlash('danger', 'Order not found.');
            return $this->redirectToRoute('app_produit_index');
        }

        $livraison = new Livraison();
        $livraison->setCommande($commande); // Associate with the existing Commande

        $form = $this->createForm(LivraisonType::class, $livraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $livraison->setStatut(false); // Automatically set statut to false (not delivered)

            $entityManager->persist($livraison);
            $entityManager->flush();

            $session->remove('commande_id'); // Clear the commande ID from session after saving

            $this->addFlash('success', 'Delivery information saved successfully.');

            // ✅ Redirect to the map page with the delivery address
            return $this->redirectToRoute('app_map', [
                'id' => $livraison->getId(),
            ]);
        }

        return $this->render('cart/add_livraison.html.twig', [
            'form' => $form,
            'commandeId' => $commandeId,
        ]);
    }


    #[Route('/{id}', name: 'app_livraison_show', methods: ['GET'])]
    public function show(Livraison $livraison): Response
    {
        return $this->render('livraison/show.html.twig', [
            'livraison' => $livraison,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_livraison_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livraison $livraison, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LivraisonType::class, $livraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the status to 1 before saving
            $livraison->setStatut(1);

            $entityManager->flush();

            return $this->redirectToRoute('app_livraison_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('livraison/edit.html.twig', [
            'livraison' => $livraison,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_livraison_delete', methods: ['POST'])]
    public function delete(Request $request, Livraison $livraison, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livraison->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($livraison);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_livraison_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/livraison/{id}/pdf', name: 'app_livraison_pdf', methods: ['GET'])]
    public function generatePdf(Livraison $livraison, Environment $twig): Response
    {
        // ✅ Render the HTML from the Twig template
        $html = $twig->render('livraison/pdf_template.html.twig', [
            'livraison' => $livraison
        ]);

        // ✅ Configure Dompdf options
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true); // ✅ Allow external images

        // ✅ Initialize Dompdf
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);

        // ✅ Set paper size
        $dompdf->setPaper('A4', 'portrait');

        // ✅ Render the HTML as PDF
        $dompdf->render();

        // ✅ Stream the generated PDF to the user
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="livraison_' . $livraison->getId() . '.pdf"',
            ]
        );
    }
    #[Route('/map/{id}', name: 'app_map', methods: ['GET'])]
    public function map(EntityManagerInterface $entityManager, int $id): Response
    {
        // ✅ Fetch Livraison using the provided ID
        $livraison = $entityManager->getRepository(Livraison::class)->find($id);

        if (!$livraison) {
            throw $this->createNotFoundException("❌ Livraison introuvable !");
        }

        // ✅ Get the delivery address directly from Livraison
        $deliveryAddress = $livraison->getAdresse(); // Ensure 'adresse' exists in Livraison entity

        return $this->render('map/index.html.twig', [
            'livraison' => $livraison,
            'deliveryAddress' => $deliveryAddress, // ✅ Pass the address to Twig
        ]);
    }


}
