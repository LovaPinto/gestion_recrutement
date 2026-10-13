<?php
namespace App\Form;

use App\Entity\Department;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class DepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
$builder
    ->add('departmentName', TextType::class, [
        'label' => 'Nom du département',
    ])

    // 👤 Manager infos (NON MAPPÉES)
    ->add('managerFirstName', TextType::class, [
        'mapped' => false,
        'label' => 'Prénom du manager',
    ])
    ->add('managerLastName', TextType::class, [
        'mapped' => false,
        'label' => 'Nom du manager',
    ])
    ->add('managerEmail', EmailType::class, [
        'mapped' => false,
        'label' => 'Email du manager',
    ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Department::class]);
    }
}
