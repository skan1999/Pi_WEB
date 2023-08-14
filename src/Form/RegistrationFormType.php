<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom')
        ->add('prenom')
        
        ->add('img', FileType::class, [
            'label' => 'Image (JPG file)',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([

                    'mimeTypesMessage' => 'Please upload a valid JPG document',
                ])
            ],

        ])
        ->add('date_naissance', DateType::class, [
            'label' => 'Date of Birth',
            'widget' => 'single_text', // Renders the input as a single text field
            'required' => true,
            'attr' => [
                'class' => 'form-control', // Add any additional classes or attributes as needed
                'placeholder' => 'YYYY-MM-DD', // Provide a placeholder format if desired
            ],])
            ->add('email')
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female',
                    'Other' => 'other',
                ],
                'placeholder' => 'Select genre',
                'required' => true,
                'attr' => ['class' => 'form-control custom-form'], // Add any additional classes or attributes as needed
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Role',
                'choices' => [
                    'Male' => 'Driver',
                    'Female' => 'Passenger',
                   
                ],
                'placeholder' => 'Select Role',
                'required' => true,
                'attr' => ['class' => 'form-control custom-form'], // Add any additional classes or attributes as needed
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
