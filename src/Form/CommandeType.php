<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $commande = $options['data'] ?? null;

        $builder
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('total')
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Not Treated' => 'Not Treated',
                    'Non Confirmé' => 'Non Confirmé',
                    'Confirmé' => 'Confirmé',
                ],
                'data' => $commande && $commande->getStatut() ? $commande->getStatut() : 'Not Treated', // Ensure a default value
                'required' => true,
                'attr' => ['class' => 'form-select'],
            ])
;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
