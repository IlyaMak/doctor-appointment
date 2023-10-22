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
use Symfony\Contracts\Translation\TranslatorInterface;

class ScheduleSlotGenerationFormType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

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
                    'label' => $this->translator->trans('start_date_label'),
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
                    'label' => $this->translator->trans('end_date_label'),
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
                    'label' => $this->translator->trans('start_time_label'),
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
                    'label' => $this->translator->trans('end_time_label'),
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
                    'label' => $this->translator->trans('patient_service_interval_label'),
                ],
            )
            ->add(
                'startLunchTime',
                TimeType::class,
                [
                    'label' => $this->translator->trans('start_lunch_date_label'),
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
                    'label' => $this->translator->trans('end_lunch_date_label'),
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
                    'label' => $this->translator->trans('monday_label'),
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
                    'label' => $this->translator->trans('tuesday_label'),
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
                    'label' => $this->translator->trans('wednesday_label'),
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
                    'label' => $this->translator->trans('thursday_label'),
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
                    'label' => $this->translator->trans('friday_label'),
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
                    'label' => $this->translator->trans('saturday_label'),
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
                    'label' => $this->translator->trans('sunday_label'),
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
                    'label' => $this->translator->trans('price_label'),
                    'mapped' => false,
                    'currency' => 'USD',
                    'data' => '5',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => '5',
                    ],
                    'constraints' => [
                        new Positive([
                            'message' => $this->translator->trans('positive_number_constraint_message'),
                        ]),
                        new NotBlank([
                            'message' => $this->translator->trans('positive_number_constraint_message'),
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
