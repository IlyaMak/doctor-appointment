<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationTypeFormType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $types = [
            $this->translator->trans('patient_user_label') => 1,
            $this->translator->trans('doctor_user_label') => 0
        ];

        $builder
            ->add(
                'type',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'choices' => $types,
                    'choice_attr' => array_map(function () {
                        return ['onchange' => 'this.form.submit()'];
                    }, $types),
                    'data' => 1
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'mt-3'],
        ]);
    }
}
