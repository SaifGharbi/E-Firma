<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\MailService;

#[Route('/produit')]
final class ProduitController extends AbstractController
{
    #[Route(name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/test',name: 'app_produit_index', methods: ['GET'])]
    public function index1(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/_content.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }


    #[Route('/back/admin',name: 'app_produit_index_admin', methods: ['GET'])]
    public function indexback(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/_content_back.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

 
    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailService $mailService): Response
    {
        $produit = new Produit();
        
        // Get the current user from the session/security context
        $user = $this->getUser();
        
        if (!$user) {
            // Redirect to login if no user is logged in
            return $this->redirectToRoute('app_login');
        }
    
        // Automatically set the current user to the product
        $produit->setUser($user);
    
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['image']->getData();
            if ($file) {
                $fileName = uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('image_directory'), $fileName);
                $produit->setImage($fileName);
            }
    
            $entityManager->persist($produit);
            $entityManager->flush();
    
            // Send Email Notification
            $mailService->sendNewProductEmail(
                'nourane.lammouchi@esprit.tn', // Recipient Email
                $produit->getNom(),
                $produit->getDescription(),
                $produit->getPrix()
            );
    
            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    ////2rakht fazet image hne 

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['image']->getData();

            // Check if a new file was uploaded
            if ($file) {
                $fileName = uniqid().'.'.$file->guessExtension();
                // Move the file to the directory where your images are stored
                $file->move(
                    $this->getParameter('image_directory'),
                    $fileName
                );
                // Set the 'image' property with the file name
                $produit->setImage($fileName);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/stats/stat', name: 'app_produit_stats', methods: ['GET'])]
    public function stats(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/stats.html.twig', [
            'productStats' => $produitRepository->getProductStatsByCategory(),
            'totalProducts' => $produitRepository->getTotalProducts(),
            'avgPrice' => $produitRepository->getAverageProductPrice(),
            'topCategories' => $produitRepository->getTopCategories(),
        ]);
    }



    //////1-my produit display my products

    #[Route('/my-products/me', name: 'app_produit_my_products', methods: ['GET'])]
    public function myProducts(ProduitRepository $produitRepository): Response
    {
        // Get the current user from the session/security context
        $user = $this->getUser();

        if (!$user) {   
            // Redirect to login if no user is logged in
            return $this->redirectToRoute('app_login');
        }

        // Get the user ID
        $userId = $user->getId();

        // Fetch products for this user
        $produits = $produitRepository->findBy(['user' => $userId]);


        ////amlt page jdida taht folder mtaa produit
        return $this->render('produit/my_products.html.twig', [
            'produits' => $produits,
        ]);
    }

}
