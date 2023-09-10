<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PatientRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Name',
                    'label_attr' => ['for' => 'name'],
                    'attr' => [
                        'placeholder' => 'name',
                        'class' => 'form-control',
                    ],
                ],
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Email',
                    'label_attr' => ['for' => 'email'],
                    'attr' => [
                        'placeholder' => 'example@gmail.com',
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new Email([
                            'message' => 'Please enter a valid email',
                        ]),
                    ],
                ],
            )
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => 'Password',
                    'label_attr' => ['for' => 'plainPassword'],
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'new-password',
                    'placeholder' => 'password',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
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
