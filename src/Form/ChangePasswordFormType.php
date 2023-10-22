<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangePasswordFormType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

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
                        'label' => $this->translator->trans('new_password_label'),
                        'row_attr' => [
                            'class' => 'form-floating',
                        ],
                        'attr' => [
                            'autocomplete' => 'new-password',
                            'placeholder' => 'newPassword',
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
                                'max' => 4096,
                            ]),
                        ],
                    ],
                    'second_options' => [
                        'label' => $this->translator->trans('repeat_password_label'),
                        'row_attr' => [
                            'class' => 'form-floating mt-3',
                        ],
                        'attr' => [
                            'autocomplete' => 'new-password',
                            'placeholder' => 'repeatPassword',
                        ],
                    ],
                    'invalid_message' => $this->translator->trans('passwords_not_matched_message'),
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
