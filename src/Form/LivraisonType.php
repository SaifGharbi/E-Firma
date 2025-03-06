<?php

namespace App\Form;

use App\Entity\Livraison;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType; // Add this import
use Symfony\Component\Form\Extension\Core\Type\TextType; // Optional, if you want to explicitly use TextType for adresse

class LivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adresse', TextType::class, [
                'label' => 'Delivery Address',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter delivery address'],
                'required' => true,
            ])
            ->add('dateLivraison', DateType::class, [
                'label' => 'Delivery Date',
                'widget' => 'single_text', // Valid for DateType
                'attr' => ['class' => 'form-control', 'type' => 'date'],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livraison::class,
        ]);
    }
}