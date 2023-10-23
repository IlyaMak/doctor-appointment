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
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactFormType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'topic',
                TextType::class,
                [
                    'label' => $this->translator->trans('topic_label'),
                    'attr' => [
                        'placeholder' => '',
                    ],
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'minMessage' => $this->translator->trans(
                                'length_constraint_topic_message',
                                ['limit' =>  '{{ limit }}']
                            ),
                            'max' => 50,
                        ]),
                    ],
                ],
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => $this->translator->trans('email_label'),
                    'attr' => [
                        'placeholder' => '',
                    ],
                    'constraints' => [
                        new Email([
                            'message' => $this->translator->trans('email_constraint_message'),
                        ]),
                    ],
                ],
            )
            ->add(
                'message',
                TextareaType::class,
                [
                    'label' => $this->translator->trans('message_label'),
                    'attr' => [
                        'placeholder' => '',
                    ],
                    'constraints' => [
                        new Length([
                            'min' => 3,
                            'minMessage' => $this->translator->trans(
                                'length_constraint_message',
                                ['limit' => '{{ limit }}']
                            ),
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
