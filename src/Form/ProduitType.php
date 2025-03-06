<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\ProduitCategorie;
use App\Entity\User;
use App\Form\DataTransformer\FileToStringTransformer; // Import the new transformer
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ✅ Nom du produit
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom du produit'],
            ])

            // ✅ Quantité
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => ['class' => 'form-control', 'min' => 1],
            ])

            // ✅ Prix
            ->add('prix', MoneyType::class, [
                'label' => 'Prix (EUR)',
                'currency' => 'EUR',
                'attr' => ['class' => 'form-control'],
            ])

            // ✅ Description
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ajoutez une description du produit'],
            ])

            // ✅ Image Upload
            ->add('image', FileType::class, [
                'label' => 'Course Image',
                'mapped' => true,
                'required' => false,
            ])
            ->add('categorie', EntityType::class, [
                'class' => ProduitCategorie::class,
                'choice_label' => 'nom', // or any field from ProduitCategorie
                'label' => 'Catégorie',
                'required' => false,
                'placeholder' => 'Sélectionnez une catégorie', // optional
            ]);

        // Add the data transformer to the 'image' field
        $builder->get('image')->addModelTransformer(new FileToStringTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}