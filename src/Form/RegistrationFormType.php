<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new Email([
                        'message' => 'Email введен не корректно!',
                    ]),
                    new Length([
                        'max' => 180,
                    ]),
                ],
                'label' => 'Электронная почта',
                'attr' => [
                    'class' => 'validate',
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Необходимо ваше соглашение с использованием персональных данных',
                    ]),
                ],
                'label' => 'Согласен с использованием моих персональных данных',
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Пароль',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите пароль',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                        'maxMessage' => 'Максимальное число символов - {{ limit }}',
                    ]),
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Выберите пол',
                'choices' => [
                    'Мужской' => 'male',
                    'Женский' => 'female',
                ],
            ])
            ->add('name', null, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Минимальное число символов - {{ limit }}',
                        'max' => 50,
                        'maxMessage' => 'Максимальное число символов - {{ limit }}',
                    ]),
                    new Regex([
                        'match' => true,
                        'pattern' => '/^[а-яё -]+$/ui',
                        'message' => 'Допустимо написание русскими буквами,пробелами и дефисами',
                    ]),
                ],
                'label' => 'Имя',
                'attr' => [
                    'class' => 'validate',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
