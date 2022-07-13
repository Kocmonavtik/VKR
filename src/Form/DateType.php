<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*$builder
            ->add('dateFirst', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'label' => "от",
                'mapped' => true
            ])
        ;*/
        $builder->add('dateFirst', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
            'widget' => 'single_text',
          /*  'years' => range(date('Y'), date('Y') + 100),
            'months' => range(date('m'), 12),
            'days' => range(date('d'), 31),*/
            'attr' => ['id' => 'dateFirst']
        ))
        ->add('dateSecond', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
            'widget' => 'single_text',
          /*  'years' => range(date('Y'), date('Y') + 100),
            'months' => range(date('m'), 12),
            'days' => range(date('d'), 31),*/
            'attr' => ['id' => 'dateSecond']
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
