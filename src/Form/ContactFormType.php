<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'topic',
                TextType::class,
                [
                    'attr' => [
                        'placeholder' => '',
                    ],
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Your topic should be at least {{ limit }} characters',
                            'max' => 50,
                        ]),
                    ],
                ],
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'attr' => [
                        'placeholder' => '',
                    ],
                    'constraints' => [
                        new Email([
                            'message' => 'Please enter a valid email',
                        ]),
                    ],
                ],
            )
            ->add(
                'message',
                TextareaType::class,
                [
                    'attr' => [
                        'placeholder' => '',
                    ],
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Your message should be at least {{ limit }} characters',
                            'max' => 4096,
                        ]),
                    ],
                ],
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
