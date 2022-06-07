<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Product;
use App\Entity\Store;
use App\Form\Type\AdditionalChoiceType;
use App\Repository\AdditionalInfoRepository;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use App\Entity\AdditionalInfo;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextType::class, [
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
            ->add('AdditionalInfo', EntityType::class, [
                'label' => 'Магазин',
                'class' => AdditionalInfo::class,
                'choice_label' => 'store.nameStore',
                'query_builder' => function (AdditionalInfoRepository $air) use ($options) {
                                return $air->createQueryBuilder('air')
                                    ->where('air.product = :product')
                                    ->setParameter('product', $options['product'])
                                    ->orderBy('air.price', 'ASC');
                }
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'product' => null
        ]);
    }
}
