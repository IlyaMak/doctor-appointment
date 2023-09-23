<?php

namespace App\Form;

use App\Entity\Specialty;
use App\Entity\User;
use App\Model\DoctorModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseDoctorFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'specialty',
                EntityType::class,
                [
                    'class' => Specialty::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Choose a specialty...',
                    'attr' => [
                        'class' => 'py-0',
                        'onchange' => 'this.form.submit()',
                    ],
                ],
            )
        ;

        /** @var DoctorModel */
        $doctorModel = $options['data'];
        $specialty = $doctorModel->specialty;
        $doctors = null === $specialty ? [] : $specialty->getDoctors();

        $builder->add(
            'doctor',
            EntityType::class,
            [
                'class' => User::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a doctor...',
                'choices' => $doctors,
                'attr' => [
                    'class' => 'py-0',
                    'onchange' => 'this.form.submit()',
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DoctorModel::class,
        ]);
    }
}
