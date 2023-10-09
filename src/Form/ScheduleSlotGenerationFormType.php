<?php

namespace App\Form;

use App\Service\ScheduleHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use DateTimeImmutable;

class ScheduleSlotGenerationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $startDateTime = new DateTimeImmutable();
        $startDateTime = $startDateTime->setTime(8, 0, 0);
        $endDateTime = $startDateTime->modify('+7 day');
        $endDateTime = $endDateTime->setTime(17, 0, 0);
        $builder
            ->add(
                'startDate',
                DateType::class,
                [
                    'mapped' => false,
                    'label' => 'Start date (included)',
                    'years' => [date('Y'), date('Y') + 1],
                    'data' => $startDateTime,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
            )
            ->add(
                'endDate',
                DateType::class,
                [
                    'mapped' => false,
                    'label' => 'End date (included)',
                    'data' => $endDateTime,
                    'years' => [date('Y'), date('Y') + 1],
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
            )
            ->add(
                'startTime',
                TimeType::class,
                [
                    'hours' => ScheduleHelper::getAvailableIntHours(),
                    'minutes' => range(0, 55, 5),
                    'data' => $startDateTime,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
            )
            ->add(
                'endTime',
                TimeType::class,
                [
                    'hours' => ScheduleHelper::getAvailableIntHours(),
                    'minutes' => range(0, 55, 5),
                    'data' => $endDateTime,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
            )
            ->add(
                'patientServiceInterval',
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
                    'data' => '30',
                    'label' => 'Patient service interval (in minutes)',
                ],
            )
            ->add(
                'startLunchTime',
                TimeType::class,
                [
                    'hours' => range(11, 15),
                    'minutes' => range(0, 55, 5),
                    'data' => (new DateTimeImmutable())->setTime(12, 0, 0),
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
            )
            ->add(
                'endLunchTime',
                TimeType::class,
                [
                    'hours' => range(11, 15),
                    'minutes' => range(0, 55, 5),
                    'data' => (new DateTimeImmutable())->setTime(13, 0, 0),
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
            )
            ->add(
                'monday',
                CheckboxType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-check-input',
                    ],
                ],
            )
            ->add(
                'tuesday',
                CheckboxType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-check-input',
                    ],
                ],
            )
            ->add(
                'wednesday',
                CheckboxType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-check-input',
                    ],
                ],
            )
            ->add(
                'thursday',
                CheckboxType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-check-input',
                    ],
                ],
            )
            ->add(
                'friday',
                CheckboxType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-check-input',
                    ],
                ],
            )
            ->add(
                'saturday',
                CheckboxType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-check-input',
                        'checked' => true,
                    ],
                ],
            )
            ->add(
                'sunday',
                CheckboxType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-check-input',
                        'checked' => true,
                    ],
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
