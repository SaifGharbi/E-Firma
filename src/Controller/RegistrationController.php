<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Hash password
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
    
            $user->setNom($form->get('nom')->getData());
            $user->setRole($form->get('role')->getData());
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            // ✅ Generate Signed URL and Send Email
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('verification@efirma.com', 'E-Firma'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm Your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
    
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
    



#[Route('/verify/email', name: 'app_verify_email')]
public function verifyUserEmail(Request $request, TranslatorInterface $translator, EntityManagerInterface $entityManager): Response
{
    try {
        $userId = $request->query->get('id');
        if (!$userId) {
            throw new \Exception("User ID missing in request.");
        }

        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            throw new \Exception("User not found.");
        }


        // Mark user as verified
        $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Your email has been successfully verified.');
    } catch (VerifyEmailExceptionInterface $exception) {
        $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
        return $this->redirectToRoute('app_register');
    } catch (\Exception $e) {
        $this->addFlash('verify_email_error', $e->getMessage());
        return $this->redirectToRoute('app_register');
    }

    return $this->redirectToRoute('app_home');
}


}
