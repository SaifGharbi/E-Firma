<?php

namespace App\Controller;

use App\Entity\ProduitCategorie;
use App\Form\ProduitCategorieType;
use App\Repository\ProduitCategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produit/categorie')]
final class ProduitCategorieController extends AbstractController
{
    #[Route(name: 'app_produit_categorie_index', methods: ['GET'])]
    public function index(ProduitCategorieRepository $produitCategorieRepository): Response
    {
        $categories = $produitCategorieRepository->findAll();

        $template = $this->isGranted('ROLE_ADMIN') 
            ? 'produit_categorie/index_admin.html.twig'
            : 'produit_categorie/index.html.twig';

        return $this->render($template, [
            'produit_categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'app_produit_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produitCategorie = new ProduitCategorie();
        $form = $this->createForm(ProduitCategorieType::class, $produitCategorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produitCategorie);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit_categorie/new.html.twig', [
            'produit_categorie' => $produitCategorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_categorie_show', methods: ['GET'])]
    public function show(ProduitCategorie $produitCategorie): Response
    {
        return $this->render('produit_categorie/show.html.twig', [
            'produit_categorie' => $produitCategorie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProduitCategorie $produitCategorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitCategorieType::class, $produitCategorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit_categorie/edit.html.twig', [
            'produit_categorie' => $produitCategorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, ProduitCategorie $produitCategorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produitCategorie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($produitCategorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
