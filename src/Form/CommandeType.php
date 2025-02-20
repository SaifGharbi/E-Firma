<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text',
                'disabled' => true, // Disable input (auto-filled)
            ])
            ->add('total', null, [
                'disabled' => true, // Disable input (auto-filled)
            ])
            ->add('statut', null, [
                'disabled' => true, // Hidden in the form
                'attr' => ['style' => 'display:none;'], // Ensure it doesn’t appear
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
