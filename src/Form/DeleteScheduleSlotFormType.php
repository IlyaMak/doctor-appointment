<?php

namespace App\Form;

use App\Service\ScheduleHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use DateTimeImmutable;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class DeleteScheduleSlotFormType extends AbstractType
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
                DateTimeType::class,
                [
                    'mapped' => false,
                    'label' => 'Start date (included)',
                    'data' => $startDateTime,
                    'years' => [date('Y'), date('Y') + 1],
                    'hours' => ScheduleHelper::getAvailableIntHours(),
                    'minutes' => range(0, 55, 5),
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
            )
            ->add(
                'endDate',
                DateTimeType::class,
                [
                    'mapped' => false,
                    'label' => 'Start date (included)',
                    'data' => $endDateTime,
                    'years' => [date('Y'), date('Y') + 1],
                    'hours' => ScheduleHelper::getAvailableIntHours(),
                    'minutes' => range(0, 55, 5),
                    'attr' => [
                        'class' => 'form-control',
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
