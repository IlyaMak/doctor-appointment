<?php

namespace App\Form;

use App\Service\ScheduleHelper;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class SingleScheduleSlotGenerationFormType extends AbstractType
{
    /** @param array<string, array<string, string>> $options */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        $date = $data['date'];
        $hour = $data['hour'];
        $startMinutes = $data['startMinutes'];
        $currentDateTime = new DateTime($date);
        $currentDateTime->setTime((int) $hour, (int) $startMinutes);
        $builder
            ->add(
                'date',
                DateType::class,
                [
                    'mapped' => false,
                    'years' => [date('Y'), date('Y') + 1],
                    'data' => $currentDateTime,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
            )
            ->add(
                'time',
                TimeType::class,
                [
                    'hours' => ScheduleHelper::getAvailableIntHours(),
                    'minutes' => range(0, 55, 5),
                    'data' => $currentDateTime,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
            )
            ->add(
                'duration',
                ChoiceType::class,
                [
                    'mapped' => false,
                    'choices' => [
                        '15' => '15',
                        '30' => '30',
                        '45' => '45',
                        '60' => '60',
                        '75' => '75',
                        '90' => '90',
                        '105' => '105',
                        '120' => '120',
                    ],
                    'data' => '15',
                    'label' => 'Duration (in minutes)',
                ],
            )
            ->add(
                'price',
                MoneyType::class,
                [
                    'mapped' => false,
                    'currency' => 'USD',
                    'data' => '5',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => '5',
                    ],
                    'constraints' => [
                        new Positive([
                            'message' => 'Please enter positive number',
                        ]),
                        new NotBlank([
                            'message' => 'Please enter positive number',
                        ]),
                    ],
                ],
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'mt-4 mx-2'],
        ]);
    }
}
