<?php
// src/Form/RecruitmentRequestType.php
namespace App\Form;

use App\Entity\RecruitmentRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RecruitmentRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('poste', TextType::class, [
                'label' => 'Poste recherché'
            ])
            ->add('typeContrat', ChoiceType::class, [
                'label' => 'Type de contrat',
                'choices' => [
                    'CDI' => 'CDI',
                    'CDD' => 'CDD',
                    'Stage' => 'Stage',
                    'Freelance' => 'Freelance',
                ]
            ])
            ->add('nombrePostes', IntegerType::class, [
                'label' => 'Nombre de postes'
            ])
            ->add('experienceSouhaitee', ChoiceType::class, [
                'label' => 'Niveau d’expérience',
                'choices' => [
                    'Junior' => 'Junior',
                    'Confirmé' => 'Confirmé',
                    'Senior' => 'Senior',
                ]
            ])
            ->add('justification', TextareaType::class, [
                'label' => 'Justification du besoin',
                'attr' => ['rows' => 5]
            ]);
    }
}
