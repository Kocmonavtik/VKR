<?php

namespace App\Form\Type;

use App\Entity\Product;
use App\Entity\Property;
use Doctrine\DBAL\Types\IntegerType;
use phpDocumentor\Reflection\PseudoTypes\IntegerRange;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('selectSort', ChoiceType::class, [
                'choices' => [
                    'Рейтингу' => 'rating',
                    'Цене по возрастанию' => 'priceUp',
                    'Цене по убыванию' => 'priceDown',
                ],
                'attr' => [
                    'class' => 'form-select',
                    'aria-label' => 'Sort',
                    'id' => 'selectSort'
                ],
                'label' => 'Отсортировать по:'
            ])
           ->add('minPriceValue', NumberType::class, [
               'empty_data' => '0',
               'required' => false,
               'attr' => (array(
                   'placeholder' => '0'
               )),
              /* 'constraints' => array(new Regex("[(\d) ]*")),*/
               'label' => 'Мин'
           ])
            ->add('maxPriceValue', NumberType::class, [
                'empty_data' => '1000000',
                'required' => false,
                'attr' => (array(
                    'placeholder' => '1000000'
                )),
               /* 'constraints' => array(new Regex("[(\d) ]*")),*/
                'label' => 'Макс'
            ])
            ->add('manufacturer', ChoiceType::class, [
                'choices' => $this->getManufacturer($options),
                'multiple' => true,
                'expanded' => true,
                'label' => 'Производитель'
            ])
            ->add('category', ChoiceType::class, [
                'choices' => $this->getCategory($options),
                'multiple' => true,
                'expanded' => true,
                'label' => 'Подкатегория'
            ]);
        $properties = $options['properties'];
        foreach ($properties as $key => $value) {
            $property[$key] = $key;
        }
        $i = 0;
        foreach ($property as $name) {
            $property2 = array();
            foreach ($properties[$name] as $values) {
                $property2[$name][$values] = $name . "/$values";
            }
            $builder->add("property_$i", ChoiceType::class, [
                'choices' => $property2,
                'multiple' => true,
                'expanded' => true,
                'label' => $name
            ]);
            $i++;
        }
        $builder->add("submit", SubmitType::class, ['label' => "Применить"]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'manufacturer' => null,
            'category' => null,
            'properties' => null
        ]);
    }
    private function getManufacturer($options): array
    {
        $manufacturer = array();
        $manufacturers = $options['manufacturer'];
        foreach ($manufacturers as $item) {
            $manufacturer[$item] = $item;
        }
        return $manufacturer;
    }

    private function getCategory($options): array
    {
        $category = array();
        $categories = $options['category'];
        foreach ($categories as $item) {
            $name = $item->getName();
            $category[$name] = $name;
        }
        //var_dump($category);
        return $category;
    }
   /* private function getProperties($options): array
    {
        $property = array();
        $properties = $options['properties'];
        $property2 = array();
        foreach ($properties as $key => $value) {
            $property[$key] = $key;
            //foreach ()
        }
        foreach ($property as $name) {
            foreach ($properties[$name] as $values) {
                $property2[$name][$values] = $name . "/$values";
            }
        }
        //var_dump($property2);
        return $property2;
    }*/
}
