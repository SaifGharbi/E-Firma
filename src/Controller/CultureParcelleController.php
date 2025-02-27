<?php

namespace App\Controller;

use App\Entity\CultureParcelle;
use App\Entity\Parcelle;
use App\Form\CultureParcelleType;
use App\Repository\CultureParcelleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/culture/parcelle')]
final class CultureParcelleController extends AbstractController
{
    #[Route(name: 'app_culture_parcelle_index', methods: ['GET'])]
    public function index(CultureParcelleRepository $cultureParcelleRepository): Response
    {
        $cultureParcelles = $cultureParcelleRepository->findAll();
        
        // Choose template based on user role
        $template = $this->isGranted('ROLE_ADMIN') 
            ? 'culture_parcelle/index_admin.html.twig'
            : 'culture_parcelle/index.html.twig';
        
        return $this->render($template, [
            'culture_parcelles' => $cultureParcelles,
        ]);
    }

    #[Route('/new/{parcelle_id}', name: 'app_culture_parcelle_new', methods: ['GET', 'POST'])]
    public function new(int $parcelle_id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $parcelle = $entityManager->getRepository(Parcelle::class)->find($parcelle_id);

    // If the parcelle does not exist, handle the error
    if (!$parcelle) {
        throw $this->createNotFoundException('Parcelle not found');
    }

        $cultureParcelle = new CultureParcelle();
        $cultureParcelle->setParcelle($parcelle);
        $form = $this->createForm(CultureParcelleType::class, $cultureParcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cultureParcelle);
            $entityManager->flush();

            return $this->redirectToRoute('app_culture_parcelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('culture_parcelle/new.html.twig', [
            'culture_parcelle' => $cultureParcelle,
            'parcelle' => $parcelle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_culture_parcelle_show', methods: ['GET'])]
    public function show(CultureParcelle $cultureParcelle): Response
    {
        return $this->render('culture_parcelle/show.html.twig', [
            'culture_parcelle' => $cultureParcelle,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_culture_parcelle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CultureParcelle $cultureParcelle, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CultureParcelleType::class, $cultureParcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_culture_parcelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('culture_parcelle/edit.html.twig', [
            'culture_parcelle' => $cultureParcelle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_culture_parcelle_delete', methods: ['POST'])]
    public function delete(Request $request, CultureParcelle $cultureParcelle, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cultureParcelle->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cultureParcelle);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_culture_parcelle_index', [], Response::HTTP_SEE_OTHER);
    }
}
