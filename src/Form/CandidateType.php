<?php

namespace App\Form;

use App\Entity\Candidate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('email', TextType::class)
            ->add('telephone', TextType::class)
            ->add('adresse', TextType::class, ['required' => false])
            ->add('ville', TextType::class, ['required' => false])
            ->add('codePostal', TextType::class, ['required' => false])
            ->add('linkedin', TextType::class, ['required' => false])
            ->add('facebook', TextType::class, ['required' => false])
            ->add('nationalite', TextType::class, ['required' => false])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Célibataire' => 'celibataire',
                    'Marié(e)' => 'marie',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'Sélectionnez votre statut',
                'required' => false,
            ])
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'Sélectionnez votre genre',
                'required' => false,
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('cvFile', FileType::class, ['required' => false])
            ->add('lmFile', FileType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidate::class,
        ]);
    }
}
