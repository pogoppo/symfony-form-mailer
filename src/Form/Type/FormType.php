<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FormType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('name', TextType::class, [
        'constraints' => [
          new Assert\NotBlank,
        ]
      ])
      ->add('gender', ChoiceType::class, [
        'choices' => [
          'Male' => 'male',
          'Female' => 'female',
          'Other' => 'other',
        ],
      ])
      ->add('email', EmailType::class, [
        'constraints' => [
          new Assert\NotBlank,
          new Assert\Email([
            'mode' => 'html5',
          ])
        ]
      ])
      ->add('tel', TelType::class)
      ->add('body', TextType::class, [
        'constraints' => [
          new Assert\NotBlank,
        ]
      ])
      ->add('agreement', CheckboxType::class, [
        'constraints' => [
          new Assert\NotBlank,
          new Assert\IsTrue,
        ]
      ])
      ->add('recaptchaV3', TextType::class, [
        'constraints' => [
          new Assert\NotBlank,
        ]
      ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([]);
  }
}
