<?php
namespace App\Form;

use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr'  => ['placeholder' => 'Prénom'],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr'  => ['placeholder' => 'Nom'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr'  => ['placeholder' => 'email@example.com'],
            ])
            ->add('password', PasswordType::class, [
                'label'  => 'Mot de passe',
                'mapped' => false,
                'attr'   => [
                    'placeholder' => '**********',
                ],
            ])
            ->add('role', EntityType::class, [
                'class'        => Role::class,
                'choice_label' => 'type',
                'placeholder'  => 'Sélectionner un rôle',
                'required'     => true,
            ]);
    }
}
