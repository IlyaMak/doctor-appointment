<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'error_bubbling' => true,
                    'first_options' => [
                        'label' => 'New password',
                        'row_attr' => [
                            'class' => 'form-floating',
                        ],
                        'attr' => [
                            'autocomplete' => 'new-password',
                            'placeholder' => 'newPassword',
                        ],
                        'constraints' => [
                            new NotBlank([
                                'message' => 'Please enter a password',
                            ]),
                            new Length([
                                'min' => 6,
                                'minMessage' => 'Your password should be at least {{ limit }} characters',
                                'max' => 4096,
                            ]),
                        ],
                    ],
                    'second_options' => [
                        'label' => 'Repeat Password',
                        'row_attr' => [
                            'class' => 'form-floating mt-3',
                        ],
                        'attr' => [
                            'autocomplete' => 'new-password',
                            'placeholder' => 'repeatPassword',
                        ],
                    ],
                    'invalid_message' => 'The password fields must match',
                    'mapped' => false,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'mt-3 security-form'],
        ]);
    }
}
