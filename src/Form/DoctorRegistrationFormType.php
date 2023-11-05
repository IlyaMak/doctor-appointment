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
                    'placeholder' => $this->translator->trans('specialty_placeholder'),
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
                    'label' => $this->translator->trans('avatar_label'),
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new File([
                            'mimeTypesMessage' => $this->translator->trans('avatar_constraint_message'),
                            'maxSize' => '1M'
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
