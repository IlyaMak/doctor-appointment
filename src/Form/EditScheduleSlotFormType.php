<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditScheduleSlotFormType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    /** @param array<string, array<string, string>> $options */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        $scheduleSlotRecommendation = $data['scheduleSlotRecommendation'];
        $builder
            ->add(
                'recommendation',
                TextareaType::class,
                [
                    'label' => $this->translator->trans('recommendation_label'),
                    'data' => $scheduleSlotRecommendation,
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
            'attr' => ['class' => 'mt-4 mx-2 security-form'],
        ]);
    }
}
