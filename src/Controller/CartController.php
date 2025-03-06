<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\User;
use App\Entity\Livraison; // Add this import
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twilio\Rest\Client;

class CartController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function addToCart(Produit $produit, SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);

        $productId = $produit->getId();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += 1;
        } else {
            $cart[$productId] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrix(),
                'quantity' => 1,
                'image' => $produit->getImage(),
            ];
        }

        $session->set('cart', $cart);

        return new JsonResponse(['status' => 'success', 'message' => 'Product added to cart']);
    }

    #[Route('/cart/view', name: 'cart_view', methods: ['GET'])]
    public function viewCart(SessionInterface $session): JsonResponse
    {
        return new JsonResponse($session->get('cart', []));
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function removeFromCart(int $id, SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
        }

        return new JsonResponse(['status' => 'success', 'message' => 'Product removed from cart']);
    }

    #[Route('/cart/update/{id}', name: 'cart_update', methods: ['POST'])]
    public function updateCart(int $id, Request $request, SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);
        $quantity = (int)$request->request->get('quantity', 1);

        if (isset($cart[$id])) {
            if ($quantity < 1) {
                $quantity = 1; // Prevent negative or zero quantities
            }
            $cart[$id]['quantity'] = $quantity;
            $session->set('cart', $cart);
        }

        return new JsonResponse(['status' => 'success', 'message' => 'Quantity updated']);
    }

    #[Route('/cart/checkout', name: 'cart_checkout', methods: ['POST'])]
    public function checkout(SessionInterface $session, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Your cart is empty'], 400);
        }

        // Get the currently logged-in user from the security context
        $user = $security->getUser();
        if (!$user instanceof User) { // Ensure the user is authenticated and of type User
            return new JsonResponse(['status' => 'error', 'message' => 'You must be logged in to place an order'], 401);
        }

        // Create a new Commande
        $commande = new Commande();
        $commande->setDate(new \DateTime());
        $commande->setStatut('Non Confirmé'); // Match the default status in Commande entity
        $commande->setUser($user); // Associate with the authenticated user

        // Calculate total and add products to the commande
        $total = 0;
        $productDetails = ""; // To store product details for the SMS
        foreach ($cart as $item) {
            $produit = $entityManager->getRepository(Produit::class)->find($item['id']);
            if ($produit) {
                // Set the product's commande (using commande_id in produit table)
                $produit->setCommande($commande);
                $produit->setQuantite($item['quantity']); // Update the product's quantity for this order
                $total += $produit->getPrix() * $item['quantity'];

                $commande->addProduit($produit);

                // Append product details for SMS
                $productDetails .= sprintf("🛍 %s - %d x %.2f TND\n", $produit->getNom(), $item['quantity'], $produit->getPrix());
            } else {
                return new JsonResponse(['status' => 'error', 'message' => 'Product not found'], 404);
            }
        }

        // Set the total for the commande
        $commande->setTotal($total);
        $entityManager->persist($commande);
        $entityManager->flush();

        // Clear the cart session after checkout
        $session->remove('cart');

        $recipientPhoneNumber = '+21694578301'; // Hardcoded recipient phone number
        $message = sprintf(
            "📦 Order Confirmation #%d\n🗓 Date: %s\n💰 Total: %.2f TND\n%s🔗 Track Order: https://yourwebsite.com/orders/%d",
            $commande->getId(),
            $commande->getDate()->format('Y-m-d H:i'),
            $commande->getTotal(),
            $productDetails,
            $commande->getId()
        );

        try {
            $this->sendTwilioMessage($recipientPhoneNumber, $message);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Order placed, but SMS sending failed: ' . $e->getMessage()], 500);
        }

        // Store the commande ID in the session for the confirmation page
        $session->set('commande_id', $commande->getId());

        // Return a JSON response to redirect to the confirmation page
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Order placed successfully',
            'redirect_url' => $this->generateUrl('cart_confirmation', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL)
        ]);
    }

    #[Route('/cart/confirmation', name: 'cart_confirmation', methods: ['GET'])]
    public function confirmation(SessionInterface $session): \Symfony\Component\HttpFoundation\Response
    {
        $commandeId = $session->get('commande_id', null);

        if (!$commandeId) {
            $this->addFlash('danger', 'No order found in session.');
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('cart/confirmation.html.twig', [
            'commandeId' => $commandeId
        ]);
    }

    #[Route('/cart/add-livraison', name: 'cart_add_livraison', methods: ['GET', 'POST'])]
    public function addLivraison(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): \Symfony\Component\HttpFoundation\Response
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

        $form = $this->createFormBuilder($livraison)
            ->add('adresse', null, [
                'label' => 'Delivery Address',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter delivery address'],
            ])
            ->add('dateLivraison', null, [
                'label' => 'Delivery Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control', 'type' => 'date'],
            ])
            ->add('statut', null, [
                'label' => 'Delivery Status',
                'attr' => ['class' => 'form-control'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livraison);
            $entityManager->flush();

            $session->remove('commande_id'); // Clear the commande ID from session after saving

            $this->addFlash('success', 'Delivery information saved successfully.');
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('cart/add_livraison.html.twig', [
            'form' => $form,
            'commandeId' => $commandeId,
        ]);
    }

    #[Route('/cart', name: 'cart_view_page', methods: ['GET'])]
    public function viewCartPage(SessionInterface $session): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $session->get('cart', []),
        ]);
    }

    private function sendTwilioMessage(string $to, string $message): void
    {
        $twilioAccountSid = $this->getParameter('twilio_account_sid');
        $twilioAuthToken = $this->getParameter('twilio_auth_token');
        $twilioPhoneNumber = $this->getParameter('twilio_phone_number');

        $twilioClient = new Client($twilioAccountSid, $twilioAuthToken);

        $twilioClient->messages->create(
            $to, // Use the provided recipient phone number
            [
                'from' => $twilioPhoneNumber,
                'body' => $message, // Accepts a string message now
            ]
        );
    }
}