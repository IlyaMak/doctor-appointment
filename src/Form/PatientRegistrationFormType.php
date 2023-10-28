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
use Symfony\Contracts\Translation\TranslatorInterface;

class PatientRegistrationFormType extends AbstractType
{
    public function __construct(public TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => $this->translator->trans('name_label'),
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
                    'label' => $this->translator->trans('email_label'),
                    'label_attr' => ['for' => 'email'],
                    'attr' => [
                        'placeholder' => 'example@gmail.com',
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new Email([
                            'message' => $this->translator->trans('email_constraint_message'),
                        ]),
                    ],
                ],
            )
            ->add(
                'plainPassword',
                PasswordType::class,
                [
                    'mapped' => false,
                    'label' => $this->translator->trans('password_label'),
                    'label_attr' => ['for' => 'plainPassword'],
                    'attr' => [
                        'class' => 'form-control',
                        'autocomplete' => 'new-password',
                        'placeholder' => 'password',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => $this->translator->trans('not_blank_constraint_password_message'),
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => $this->translator->trans(
                                'length_constraint_password_message',
                                ['limit' => '{{ limit }}']
                            ),
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ]
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
