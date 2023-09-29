<?php

namespace App\Form;

use App\Entity\Specialty;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DoctorRegistrationFormType extends PatientRegistrationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
            ->add(
                'specialty',
                EntityType::class,
                [
                    'class' => Specialty::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Choose a specialty...',
                    'label' => false,
                    'attr' => [
                        'class' => 'py-0 form-control',
                    ],
                ],
            )
            ->add(
                'avatar',
                FileType::class,
                [
                    'label' => 'Upload avatar',
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new File([
                            'mimeTypesMessage' => 'Please upload a valid image file',
                        ]),
                    ],
                ],
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => ['class' => 'mt-3 security-form'],
        ]);
    }
}
