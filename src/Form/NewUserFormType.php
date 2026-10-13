<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\NewUser;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomComplet')
            ->add('email')
            ->add('mdp')
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'type',
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'company_name',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewUser::class,
        ]);
    }
}
