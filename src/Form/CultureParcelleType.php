<?php

namespace App\Form;

use App\Entity\CultureParcelle;
use App\Entity\Parcelle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CultureParcelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_culture')
            ->add('date_plantation', null, [
                'widget' => 'single_text',
            ])
            ->add('date_recolte', null, [
                'widget' => 'single_text',
            ])
            ->add('rendement_estime')
            ->add('parcelle', EntityType::class, [
                'class' => Parcelle::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CultureParcelle::class,
        ]);
    }
}
