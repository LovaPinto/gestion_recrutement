<?php

namespace App\Form;

use App\Entity\Candidate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Prénom', 'autocomplete' => 'off'],
                'required' => true,
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Nom', 'autocomplete' => 'off'],
                'required' => true,
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['placeholder' => '+261 38 44 673 95', 'autocomplete' => 'off'],
                'required' => true,
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['placeholder' => 'Adresse', 'autocomplete' => 'off'],
                'required' => false,
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => ['placeholder' => 'Ville', 'autocomplete' => 'off'],
                'required' => false,
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'attr' => ['placeholder' => 'Code postal', 'autocomplete' => 'off'],
                'required' => false,
            ])
            ->add('linkedin', TextType::class, [
                'label' => 'LinkedIn',
                'attr' => [
                    'placeholder' => 'Ex: https://www.linkedin.com/in/votrenom',
                    'autocomplete' => 'off'
                ],
                'required' => false,
            ])
            ->add('facebook', TextType::class, [
                'label' => 'Facebook',
                'attr' => [
                    'placeholder' => 'Ex: https://www.facebook.com/votrenom',
                    'autocomplete' => 'off'
                ],
                'required' => false,
            ])
            ->add('nationalite', ChoiceType::class, [
                'label' => 'Nationalité',
                'choices' => [
                    'Malagasy' => 'Malagasy',
                    'Français' => 'Français',
                    'Anglais' => 'Anglais',
                    'Espagnol' => 'Espagnol',
                    'Italien' => 'Italien',
                    'Allemand' => 'Allemand',
                    'Indien' => 'Indien',
                  

                ],
                'placeholder' => 'Sélectionnez votre nationalité',
                'required' => true,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Célibataire' => 'Célibataire',
                    'Marié(e)' => 'Marié(e)',
                    'Veuf(ve)' => 'Veuf(ve)',
                    'Divorcé(e)' => 'Divorcé(e)',
                ],
                'placeholder' => 'Sélectionnez votre statut',
                'required' => true,
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ],
                'placeholder' => 'Sélectionnez votre genre',
                'required' => true,
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidate::class,
            'attr' => ['autocomplete' => 'off'],
        ]);
    }
}
