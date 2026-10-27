<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class GuestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('plainPassword', RepeatedType::class, [
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                // Le mot de passe doit être traité avant d'être attribué à l'utilisateur
                'mapped' => false,
                'invalid_message' => 'Les mots de passe doivent être identiques',
                'type' => PasswordType::class,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez entrer un mot de passe',
                    ),
                    new Length(
                        min: 6,
                        minMessage: 'Le mot de passe doit faire au minimum {{ limit }} caractères',
                        // Longueur maximale autorisée par Symfony
                        max: 4096
                    ),
                ],
            ])
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
