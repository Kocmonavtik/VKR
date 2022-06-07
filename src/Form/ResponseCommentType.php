<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Product;
use App\Entity\Store;
use App\Form\Type\AdditionalChoiceType;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\StringType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use App\Entity\AdditionalInfo;

class ResponseCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'max' => 500,
                        'maxMessage' => 'Максимальное число символов - {{ limit }}'
                    ])
                ],
                'label' => 'Текст комментария',
                'attr' => [
                    'class' => 'validate'
                ]
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
