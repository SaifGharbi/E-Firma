<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendNewProductEmail(string $to, string $productName, string $description, float $price)
    {
        $email = (new Email())
            ->from('lammouchinourane7@gmail.com')
            ->to($to)
            ->subject('🎉🚀 NOUVEAU PRODUIT DISPONIBLE! 🛍️')
            ->html("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background: #f9f9f9; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1);'>
                    <h2 style='color: #2b7bff; text-align: center;'>🔥 Un Nouveau Produit est Arrivé! 🔥</h2>
                    <p style='font-size: 16px; text-align: center;'>Tu l'attendais, et le voici! Découvrez notre toute nouvelle pépite ajoutée à notre boutique! 🎉</p>
                    
                    <div style='background: #ffffff; padding: 15px; border-radius: 8px; margin-top: 20px;'>
                        <h3 style='color: #ff9800;'>🛒 {$productName}</h3>
                        <p><strong>Description:</strong> {$description}</p>
                        <p><strong>💰 Prix:</strong> <span style='color: #4caf50; font-weight: bold;'>{$price} TND</span></p>
                    </div>

                    <div style='text-align: center; margin-top: 20px;'>
                        <a href='https://yourwebsite.com/products' style='background: #2b7bff; color: white; padding: 12px 20px; text-decoration: none; font-weight: bold; border-radius: 6px;'>🛍️ Voir le Produit</a>
                    </div>

                    <p style='font-size: 14px; color: #888; text-align: center; margin-top: 20px;'>Merci de faire partie de notre communauté! ❤️</p>
                </div>
            ");

        $this->mailer->send($email);
    }

    public function sendRendezVousConfirmationEmail(string $to, \DateTimeInterface $date, string $serviceName, bool $status): void
    {
        $statusText = $status ? 'Confirmé' : 'Non Confirmé';
        $email = (new Email())
            ->from('lammouchinourane7@gmail.com')
            ->to($to)
            ->subject('✅ Confirmation de votre Rendez-Vous')
            ->html("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background: #f9f9f9; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1);'>
                    <h2 style='color: #2b7bff; text-align: center;'>✅ Votre Rendez-Vous a été Planifié! 📅</h2>
                    <p style='font-size: 16px; text-align: center;'>Merci de nous avoir choisi. Voici les détails de votre rendez-vous :</p>
                    
                    <div style='background: #ffffff; padding: 15px; border-radius: 8px; margin-top: 20px;'>
                        <h3 style='color: #ff9800;'>📋 Détails du Rendez-Vous</h3>
                        <p><strong>Date & Heure:</strong> {{ date|date('Y-m-d H:i:s') }}</p>
                        <p><strong>Service:</strong> {$serviceName}</p>
                        <p><strong>Statut:</strong> <span style='color: #4caf50; font-weight: bold;'>{$statusText}</span></p>
                    </div>

                    <div style='text-align: center; margin-top: 20px;'>
                        <a href='https://yourwebsite.com/appointments' style='background: #2b7bff; color: white; padding: 12px 20px; text-decoration: none; font-weight: bold; border-radius: 6px;'>📅 Gérer Mes Rendez-Vous</a>
                    </div>

                    <p style='font-size: 14px; color: #888; text-align: center; margin-top: 20px;'>Si vous avez des questions, n’hésitez pas à nous contacter. ❤️</p>
                </div>
            ");

        $this->mailer->send($email);
    }

}
