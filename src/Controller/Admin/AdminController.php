<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\ParcelleRepository;

#[Route('/admin')]
class AdminController extends AbstractController
{
    
    #[Route('/', name: 'admin_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(): Response
    {
        return $this->render('Admin/dashboard.html.twig');
    }

    #[Route('/users', name: 'admin_users', methods: ['GET'])]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('Admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/parcelles', name: 'admin_parcelles', methods: ['GET'])]
    public function parcelles(ParcelleRepository $parcelleRepository): Response
    {
        return $this->render('Admin/parcelles.html.twig', [
            'parcelles' => $parcelleRepository->findAll(),
        ]);
    }
}